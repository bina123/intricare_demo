@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2 class="mb-4">Custom Fields Manager</h2>

    <div class="card mb-3">
        <div class="card-body">
            <form id="fieldForm" class="row g-3">
                @csrf
                <div class="col-md-5">
                    <label class="form-label">Field Name</label>
                    <input type="text" name="name" class="form-control">
                    <span class="text-danger error-text name_error"></span>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="text">Text</option>
                        <option value="number">Number</option>
                        <option value="date">Date</option>
                        <option value="email">Email</option>
                    </select>
                    <span class="text-danger error-text type_error"></span>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-success w-100">Add Field</button>
                </div>
            </form>
        </div>
    </div>

    <div id="fieldsTable">
        @include('custom_fields.partials.table', ['fields' => $fields])
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function() {
        // Add new field
        $('#fieldForm').on('submit', function(e) {
            e.preventDefault();
            $('.error-text').text('');

            $.ajax({
                url: "{{ route('customfields.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        alert(res.message);
                        $('#fieldForm')[0].reset();
                        loadTable();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        $.each(errors, function(key, val) {
                            $('.' + key + '_error').text(val[0]);
                        });
                    }
                }
            });
        });

        // Delete
        $(document).on('click', '.btn-delete', function() {
            if (!confirm('Are you sure?')) return;
            let id = $(this).data('id');
            $.ajax({
                url: `/custom-fields/delete/${id}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    alert(res.message);
                    loadTable();
                }
            });
        });

        function loadTable() {
            $('#fieldsTable').load(location.href + ' #fieldsTable>*', '');
        }
    });
</script>
@endpush