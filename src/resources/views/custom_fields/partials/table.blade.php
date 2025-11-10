<table class="table table-bordered align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Type</th>
            <th width="100">Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($fields as $field)
        <tr>
            <td>{{ $field->id }}</td>
            <td>{{ $field->name }}</td>
            <td>{{ ucfirst($field->type) }}</td>
            <td>
                <button class="btn btn-sm btn-danger btn-delete" data-id="{{ $field->id }}">Delete</button>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="4" class="text-center">No fields found.</td>
        </tr>
        @endforelse
    </tbody>
</table>