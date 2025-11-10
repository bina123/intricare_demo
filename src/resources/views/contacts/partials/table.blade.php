<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Gender</th>
            <th>Profile</th>
            <th>Doc</th>
            <th>Notes</th>
            <th width="150">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($contacts as $c)
        <tr>
            <td>{{ $c->id }}</td>
            <td>{{ $c->name }}</td>
            <td>{{ $c->email }}</td>
            <td>{{ $c->phone }}</td>
            <td>{{ $c->gender }}</td>
            <td>
                @if($c->profile_image)
                <img src="{{ asset('storage/'.$c->profile_image) }}" width="40" height="40" class="rounded-circle">
                @endif
            </td>
            <td>
                @if($c->additional_file)
                <a href="{{ asset('storage/'.$c->additional_file) }}" target="_blank">View</a>
                @endif
            </td>
            <td>
                @if ($c->notes)
                <button class="btn btn-outline-secondary btn-sm btn-logs"
                    data-id="{{ $c->id }}">
                    <i class="bi bi-clock-history me-1"></i> Logs
                </button>
                @else
                <span class="text-muted">—</span>
                @endif
            </td>

            <td>
                <button class="btn btn-sm btn-primary btn-edit"
                    data-id="{{ $c->id }}"
                    data-name="{{ $c->name }}"
                    data-email="{{ $c->email }}"
                    data-phone="{{ $c->phone }}"
                    data-gender="{{ $c->gender }}">
                    Edit
                </button>
                @if ($c->merged_into)
                <span class="badge bg-secondary"
                    data-bs-toggle="tooltip"
                    title="Merged into {{ $c->mergedInto?->name ?? 'Contact #' . $c->merged_into }}">
                    Merged → {{ $c->mergedInto?->name ?? '#'.$c->merged_into }}
                </span>
                @else
                <button class="btn btn-warning btn-merge" data-id="{{ $c->id }}">
                    Merge
                </button>

                @endif
                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $c->id }}">Delete</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center">No contacts found.</td>
        </tr>
        @endforelse
    </tbody>
</table>
@if ($contacts->hasPages())
<div class="d-flex justify-content-end mt-3">
    {!! $contacts->onEachSide(1)->links('pagination::bootstrap-5') !!}
</div>
@endif