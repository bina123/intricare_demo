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

<!-- Toast Container -->
<div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteConfirmLabel">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i> Confirm Delete
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this custom field? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button id="btnConfirmDelete" type="button" class="btn btn-danger">Yes, Delete</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
    $(function() {

        /** ------------------------
         *  Bootstrap Toast Helper
         * ------------------------ */
        const showToast = (message, type = 'success') => {
            const bgClass = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const toast = $(`
            <div class="toast align-items-center ${bgClass} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="d-flex">
                    <div class="toast-body">${message}</div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);

            $('#toastContainer').append(toast);
            const toastEl = new bootstrap.Toast(toast[0]);
            toastEl.show();
            toast.on('hidden.bs.toast', () => toast.remove());
        };

        /** ------------------------
         *  Add new custom field
         * ------------------------ */
        $('#fieldForm').on('submit', function(e) {
            e.preventDefault();
            $('.error-text').text('');

            $.ajax({
                url: "{{ route('customfields.store') }}",
                method: 'POST',
                data: $(this).serialize(),
                success: function(res) {
                    if (res.status === 'success') {
                        showToast(res.message, 'success');
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
                    } else {
                        showToast('An unexpected error occurred', 'error');
                    }
                }
            });
        });

        /** ------------------------
         *  Delete field (with modal)
         * ------------------------ */
        let deleteId = null;

        $(document).on('click', '.btn-delete', function() {
            deleteId = $(this).data('id');
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
            modal.show();
        });

        $('#btnConfirmDelete').on('click', function() {
            if (!deleteId) return;
            const btn = $(this).prop('disabled', true).text('Deleting...');

            $.ajax({
                url: `/custom-fields/delete/${deleteId}`,
                method: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(res) {
                    showToast(res.message, 'success');
                    loadTable();
                    bootstrap.Modal.getInstance(document.getElementById('deleteConfirmModal')).hide();
                },
                error: function() {
                    showToast('Failed to delete field', 'error');
                },
                complete: () => {
                    deleteId = null;
                    btn.prop('disabled', false).text('Yes, Delete');
                }
            });
        });

        /** ------------------------
         *  Reload table dynamically
         * ------------------------ */
        function loadTable() {
            $('#fieldsTable').load(location.href + ' #fieldsTable>*', '');
        }
    });
</script>
@endpush