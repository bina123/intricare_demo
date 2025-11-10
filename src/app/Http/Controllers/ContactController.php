<?php

namespace App\Http\Controllers;

use App\Models\{
    Contact,
    CustomField,
    ContactCustomFieldValue,
    ContactMergeLog
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{
    Storage,
    Validator,
    File
};

class ContactController extends Controller
{
    /* ============================================================
     *  MAIN VIEWS
     * ============================================================ */

    /** List contacts (with AJAX table support) */
    public function index(Request $request)
    {
        $contacts = Contact::with('mergedInto')
            ->when(!$request->filled('show_merged'), fn($q) => $q->whereNull('merged_into'))
            ->when($request->filled('name'), fn($q) => $q->where('name', 'like', "%{$request->name}%"))
            ->when($request->filled('email'), fn($q) => $q->where('email', 'like', "%{$request->email}%"))
            ->when($request->filled('gender'), fn($q) => $q->where('gender', $request->gender))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        if ($request->ajax()) {
            return view('contacts.partials.table', compact('contacts'))->render();
        }

        return view('contacts.index', [
            'contacts' => $contacts,
            'customFields' => CustomField::all(),
        ]);
    }

    /* ============================================================
     *  CRUD OPERATIONS
     * ============================================================ */

    public function store(Request $request)
    {
        $validator = $this->validateContact($request);
        if ($validator->fails()) return $this->validationError($validator);

        $data = $this->handleFileUploads($request, $validator->validated());
        $contact = Contact::create($data);
        $this->saveCustomFields($contact, $request->custom_fields ?? []);

        return $this->success('Contact created successfully');
    }

    public function update(Request $request, $id)
    {
        $contact = Contact::findOrFail($id);
        $validator = $this->validateContact($request, $id);
        if ($validator->fails()) return $this->validationError($validator);

        $data = $this->handleFileUploads($request, $validator->validated(), $contact);
        $contact->update($data);
        $this->saveCustomFields($contact, $request->custom_fields ?? []);

        return $this->success('Contact updated successfully');
    }

    public function edit($id)
    {
        $contact = Contact::with('customFieldValues.field')->findOrFail($id);
        return response()->json([
            'status' => 'success',
            'data' => [
                'id'     => $contact->id,
                'name'   => $contact->name,
                'email'  => $contact->email,
                'phone'  => $contact->phone,
                'gender' => $contact->gender,
                'custom_fields' => $contact->customFieldValues->pluck('value', 'custom_field_id'),
            ],
        ]);
    }

    public function destroy($id)
    {
        $contact = Contact::findOrFail($id);
        foreach (['profile_image', 'additional_file'] as $file) {
            if ($contact->$file) Storage::disk('public')->delete($contact->$file);
        }
        $contact->delete();
        return $this->success('Contact deleted successfully');
    }

    /* ============================================================
     *  HELPERS
     * ============================================================ */

    private function validateContact(Request $request, $id = null)
    {
        return Validator::make(
            $request->all(),
            [
                'name'  => 'required|string|max:100',
                'email' => "required|email|unique:contacts,email,$id",
                'phone' => "required|regex:/^[0-9]{10}$/|unique:contacts,phone,$id",
                'gender' => 'required|string',
                'profile_image'   => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
                'additional_file' => 'nullable|file|max:5120',
            ],
            [
                'phone.regex' => 'Phone number must be exactly 10 digits.',
                'phone.unique' => 'This phone number is already registered.',
            ]
        );
    }

    private function handleFileUploads(Request $request, array $data, ?Contact $contact = null): array
    {
        foreach (['profile_image' => 'profile_images', 'additional_file' => 'documents'] as $field => $path) {
            if ($request->hasFile($field)) {
                if ($contact?->$field) Storage::disk('public')->delete($contact->$field);
                $data[$field] = $request->file($field)->store($path, 'public');
            }
        }
        return $data;
    }

    private function saveCustomFields(Contact $contact, array $fields): void
    {
        foreach ($fields as $id => $value) {
            ContactCustomFieldValue::updateOrCreate(
                ['contact_id' => $contact->id, 'custom_field_id' => $id],
                ['value' => $value]
            );
        }
    }

    private function success(string $message)
    {
        return response()->json(['status' => 'success', 'message' => $message]);
    }

    private function validationError($validator)
    {
        return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
    }

    /* ============================================================
     *  MERGE LOGIC
     * ============================================================ */

    public function mergeModal($id)
    {
        $secondary = Contact::with('customFieldValues.field')->findOrFail($id);
        $contacts = Contact::with('customFieldValues.field')
            ->where('id', '!=', $id)
            ->whereNull('merged_into') // ✅ exclude already merged contacts
            ->get();

        return view('contacts.partials.merge-modal', compact('secondary', 'contacts'));
    }

    public function previewMerge(Request $request)
    {
        $request->validate([
            'master_id' => 'required|exists:contacts,id',
            'secondary_id' => 'required|exists:contacts,id|different:master_id',
        ]);

        $master = Contact::with('customFieldValues.field')->findOrFail($request->master_id);
        $secondary = Contact::with('customFieldValues.field')->findOrFail($request->secondary_id);

        $diffs = [
            'email' => $master->email === $secondary->email ? null : [
                'master' => $master->email,
                'secondary' => $secondary->email,
            ],
            'phone' => $master->phone === $secondary->phone ? null : [
                'master' => $master->phone,
                'secondary' => $secondary->phone,
            ],
            'custom_fields' => [],
        ];

        foreach ($secondary->customFieldValues as $fieldValue) {
            $masterValue = $master->customFieldValues->firstWhere('custom_field_id', $fieldValue->custom_field_id);
            $name = $fieldValue->field->name ?? "Custom Field #{$fieldValue->custom_field_id}";
            if (!$masterValue) {
                $diffs['custom_fields'][$fieldValue->custom_field_id] = [
                    'type' => 'missing',
                    'name' => $name,
                    'value' => $fieldValue->value,
                ];
            } elseif (trim($masterValue->value) !== trim($fieldValue->value)) {
                $diffs['custom_fields'][$fieldValue->custom_field_id] = [
                    'type' => 'conflict',
                    'name' => $name,
                    'master' => $masterValue->value,
                    'secondary' => $fieldValue->value,
                ];
            }
        }

        return response()->json(['status' => 'success', 'differences' => $diffs]);
    }

    public function performMerge(Request $request)
    {
        $master = Contact::findOrFail($request->master_contact_id);
        $secondary = Contact::findOrFail($request->secondary_id);
        $policies = $request->input('policy', []);
        $master->notes ??= '';
        $logDetails = [];

        /* Email */
        if ($master->email !== $secondary->email && isset($policies['email'])) {
            $action = match ($policies['email']) {
                'secondary' => "Replaced with {$secondary->email}",
                'both' => "Added alt email {$secondary->email}",
                default => null
            };
            if ($action) {
                $master->notes .= "\nEmail: $action";
                $logDetails['email_action'] = $action;
            }
        }

        /* Phone */
        if ($master->phone !== $secondary->phone && isset($policies['phone'])) {
            $action = match ($policies['phone']) {
                'secondary' => "Replaced with {$secondary->phone}",
                'both' => "Added alt phone {$secondary->phone}",
                default => null
            };
            if ($action) {
                $master->notes .= "\nPhone: $action";
                $logDetails['phone_action'] = $action;
            }
        }

        /* Files */
        foreach (['profile_image', 'additional_file'] as $fileField) {
            if (!$master->$fileField && $secondary->$fileField) {
                $src = storage_path('app/public/' . $secondary->$fileField);
                if (file_exists($src)) {
                    $ext = pathinfo($src, PATHINFO_EXTENSION);
                    $folder = "{$fileField}s";
                    $newPath = "{$folder}/merged_" . uniqid() . ".$ext";
                    $dest = storage_path("app/public/$newPath");
                    File::ensureDirectoryExists(dirname($dest));
                    if (File::copy($src, $dest)) {
                        $master->$fileField = $newPath;
                        $logDetails['files'][$fileField] = "Copied from secondary ({$secondary->$fileField})";
                    }
                }
            }
        }

        /* Custom Fields */
        $customChanges = [];
        foreach ($secondary->customFieldValues as $field) {
            $existing = $master->customFieldValues()->where('custom_field_id', $field->custom_field_id)->first();
            $policy = $policies[$field->custom_field_id] ?? null;
            $name = optional($field->field)->name ?? "Field {$field->custom_field_id}";

            if (!$existing) {
                $field->replicate()->fill(['contact_id' => $master->id])->save();
                $customChanges[] = "$name: copied missing value '{$field->value}'";
            } elseif ($existing->value !== $field->value) {
                $change = match ($policy) {
                    'secondary' => "$name: replaced with '{$field->value}'",
                    'both' => "$name: appended both values",
                    default => null
                };
                if ($change) {
                    $customChanges[] = $change;
                    $existing->value = $policy === 'both'
                        ? "{$existing->value} | {$field->value}"
                        : $field->value;
                    $existing->save();
                }
            }
        }

        $logDetails['custom_fields'] = $customChanges;
        $master->save();
        $secondary->update(['merged_into' => $master->id]);

        ContactMergeLog::create([
            'master_contact_id' => $master->id,
            'secondary_contact_id' => $secondary->id,
            'details' => $logDetails,
        ]);

        $master->notes .= "\nMerged contact ID {$secondary->id} on " . now()->format('Y-m-d H:i:s');
        $master->save();

        return $this->success('Contacts merged successfully, logged with structured details.');
    }

    public function mergeLogs($id)
    {
        $logs = ContactMergeLog::with('secondary')
            ->where('master_contact_id', $id)
            ->latest()
            ->get();

        return view('contacts.partials.merge-logs', compact('logs'));
    }
}
