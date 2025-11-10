<?php

namespace App\Http\Controllers;

use App\Models\CustomField;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CustomFieldController extends Controller
{
    public function index()
    {
        $fields = CustomField::latest()->get();
        return view('custom_fields.index', compact('fields'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100|unique:custom_fields,name',
            'type' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        CustomField::create([
            'name' => $request->name,
            'type' => $request->type,
        ]);

        return response()->json(['status' => 'success', 'message' => 'Custom field added successfully']);
    }

    public function destroy($id)
    {
        CustomField::findOrFail($id)->delete();
        return response()->json(['status' => 'success', 'message' => 'Field deleted successfully']);
    }
}
