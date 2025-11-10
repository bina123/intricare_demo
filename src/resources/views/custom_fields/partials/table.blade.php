<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th style="width: 60px;">#</th>
            <th>Name</th>
            <th>Type</th>
            <th width="100">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($fields as $index => $field)
        <tr>
            {{-- Auto-increment serial number --}}
            <td>{{ $loop->iteration }}</td>
            <td>{{ $field->name }}</td>
            <td>{{ ucfirst($field->type) }}</td>
            <td>
                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $field->id }}">
                    <i class="bi bi-trash"></i> Delete
                </button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center text-muted">No custom fields found.</td>
        </tr>
        @endforelse
    </tbody>
</table>