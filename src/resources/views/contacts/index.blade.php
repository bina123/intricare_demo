@extends('layouts.app')

@push('styles')

<style>
    .table-warning td,
    .table-danger td,
    .table-info td {
        vertical-align: middle;
    }

    .table-warning td:first-child,
    .table-danger td:first-child,
    .table-info td:first-child {
        font-weight: 600;
    }

    .text-center label {
        cursor: pointer;
    }

    .merged-badge {
        background-color: #6c757d !important;
        color: #fff !important;
        font-size: 0.75rem;
        padding: 0.35em 0.5em;
        border-radius: 4px;
    }

    .merged-badge:hover {
        background-color: #5a6268 !important;
    }

    .toast {
        --bs-toast-max-width: 360px;
    }
</style>

@endpush
@section('content')
<div class="container py-4">
    <h2 class="mb-4">Contact Management</h2>

    {{-- Filter Section --}}
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="row g-2">
                <div class="col-md-3">
                    <input type="text" name="name" class="form-control" placeholder="Search Name">
                </div>
                <div class="col-md-3">
                    <input type="text" name="email" class="form-control" placeholder="Search Email">
                </div>
                <div class="col-md-3">
                    <select name="gender" class="form-select" id="genderFilter">
                        <option value="">All Genders</option>
                        <option>Male</option>
                        <option>Female</option>
                        <option>Other</option>
                    </select>
                </div>
                <div class="col-md-3 text-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                    <button type="button" class="btn btn-success" id="btnAdd">+ Add Contact</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Contact Table --}}
    <div id="contactTable">

        @include('contacts.partials.table', ['contacts' => $contacts])
    </div>

</div>

{{-- Modal Form --}}
<div class="modal fade" id="contactModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="contactForm" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="contact_id" name="contact_id">
                <div class="modal-header">
                    <h5 class="modal-title">Add / Edit Contact</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control">
                            <span class="text-danger error-text name_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control">
                            <span class="text-danger error-text email_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control">
                            <span class="text-danger error-text phone_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Gender</label><br>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="Male"> Male
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="Female"> Female
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="gender" value="Other"> Other
                            </div>
                            <br />
                            <span class="text-danger error-text gender_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Profile Image</label>
                            <input type="file" name="profile_image" class="form-control">
                            <span class="text-danger error-text profile_image_error"></span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Additional File</label>
                            <input type="file" name="additional_file" class="form-control">
                            <span class="text-danger error-text additional_file_error"></span>
                        </div>

                        {{-- Dynamic Custom Fields --}}
                        @foreach($customFields as $field)
                        <div class="col-md-6">
                            <label class="form-label">{{ $field->name }}</label>
                            <input type="{{ $field->type }}" name="custom_fields[{{ $field->id }}]" class="form-control">
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="notesModalLabel">
                    <i class="bi bi-journal-text me-2"></i> Contact Notes
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="notesContent" class="p-2 text-monospace small"
                    style="white-space: pre-wrap; line-height: 1.5;"></div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(function() {

        // --- Toast helper
        const showToast = (msg, type = 'success') => {
            const bg = type === 'success' ? 'text-bg-success' : 'text-bg-danger';
            const toast = $(`
      <div class="toast align-items-center ${bg} border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true"
        style="position: fixed; top: 1rem; right: 1rem; z-index: 2000;">
        <div class="d-flex">
          <div class="toast-body">${msg}</div>
          <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
      </div>
    `);
            $('body').append(toast);
            new bootstrap.Toast(toast[0]).show();
            setTimeout(() => toast.remove(), 4000);
        };

        // --- AJAX contact loader
        const fetchContacts = (url = "{{ route('contacts.index') }}") => {
            $('#contactTable').fadeTo(100, 0.5);
            $.ajax({
                url,
                data: $('#filterForm').serialize(),
                success: html => $('#contactTable').html(html).fadeTo(100, 1),
                error: () => showToast('Error loading data', 'error')
            });
        };

        // Filters
        $('#filterForm').on('submit', e => {
            e.preventDefault();
            fetchContacts();
        });
        $('#genderFilter').on('change', function() {
            fetchContacts();
        });

        let timer;
        $('#filterForm input[type="text"]').on('keyup', function() {
            clearTimeout(timer);
            timer = setTimeout(fetchContacts, 400);
        });
        $(document).on('click', '#contactTable .pagination a', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) fetchContacts(url);
        });

        // --- Add
        $('#btnAdd').click(() => {
            $('#contactForm')[0].reset();
            $('#contact_id').val('');
            $('#contactModal').modal('show');
        });

        // --- Save
        $('#contactForm').on('submit', function(e) {
            e.preventDefault();
            $('.error-text').text('');

            const formData = new FormData(this);
            const id = $('#contact_id').val();
            const url = id ? `/contacts/update/${id}` : `{{ route('contacts.store') }}`;

            $.ajax({
                url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: res => {
                    if (res.status === 'success') {
                        $('#contactModal').modal('hide');
                        $('#filterForm').trigger('submit');
                        showToast(res.message, 'success');
                    }
                },
                error: xhr => {
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, (key, val) => $('.' + key + '_error').text(val[0]));
                    } else showToast('Something went wrong!', 'error');
                }
            });
        });

        // --- Edit
        $(document).on('click', '.btn-edit', function() {
            const id = $(this).data('id');
            const modal = $('#contactModal');
            const form = $('#contactForm')[0];
            form.reset();
            $('#contact_id').val('');

            $.get(`/contacts/${id}/edit`, res => {
                if (res.status === 'success') {
                    const d = res.data;
                    modal.find('#contact_id').val(d.id);
                    modal.find('[name="name"]').val(d.name);
                    modal.find('[name="email"]').val(d.email);
                    modal.find('[name="phone"]').val(d.phone);
                    modal.find(`[name="gender"][value="${d.gender}"]`).prop('checked', true);
                    if (d.custom_fields)
                        $.each(d.custom_fields, (fid, val) => modal.find(`[name="custom_fields[${fid}]"]`).val(val));
                    modal.modal('show');
                }
            });
        });

        // --- Delete (SweetAlert confirm)
        $(document).on('click', '.btn-delete', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Delete Contact?',
                text: 'This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then(result => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `/contacts/delete/${id}`,
                        method: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: res => {
                            showToast(res.message, 'success');
                            $('#filterForm').trigger('submit');
                        },
                        error: () => showToast('Delete failed', 'error')
                    });
                }
            });
        });

        // --- Merge logic (same as before, shortened)
        $(document).on('click', '.btn-merge', function() {
            const id = $(this).data('id');
            $.get(`/contacts/${id}/merge`, function(html) {
                $('#mergeModal').remove();
                $('body').append(html);
                new bootstrap.Modal(document.getElementById('mergeModal')).show();
            });
        });

        // Auto preview
        $(document).on('change', '#master_contact_id', function() {
            const masterId = $(this).val(),
                secondaryId = $('#secondary_id').val();
            if (!masterId || !secondaryId) return;
            $('#mergePreviewBody').html(`<tr><td colspan="4" class="text-center text-muted py-3">Generating comparison...</td></tr>`);
            $.ajax({
                url: "{{ route('contacts.previewMerge') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    master_id: masterId,
                    secondary_id: secondaryId
                },
                success: res => {
                    if (res.status !== 'success') return;

                    const tableBody = $('#mergePreviewBody');
                    tableBody.empty();

                    const addRow = (field, master, secondary, type, fieldId = null) => {
                        const decisionName = `policy[${fieldId || field}]`;
                        let options = '';
                        if (type === 'conflict') {
                            options = `
                        <div class="text-center">
                            <label class="me-2"><input type="radio" name="${decisionName}" value="master" checked> Master</label>
                            <label class="me-2"><input type="radio" name="${decisionName}" value="secondary"> Secondary</label>
                            <label><input type="radio" name="${decisionName}" value="both"> Both</label>
                        </div>
                    `;
                        } else {
                            options = '<span class="text-muted">—</span>';
                        }

                        tableBody.append(`
                    <tr class="table-${type === 'conflict' ? 'warning' : 'info'}">
                        <td><strong>${field}</strong></td>
                        <td>${master || ''}</td>
                        <td>${secondary || ''}</td>
                        <td>${options}</td>
                    </tr>
                `);
                    };

                    // Core fields
                    if (res.differences.email)
                        addRow('Email', res.differences.email.master, res.differences.email.secondary, 'conflict', 'email');
                    if (res.differences.phone)
                        addRow('Phone', res.differences.phone.master, res.differences.phone.secondary, 'conflict', 'phone');

                    // Custom fields
                    $.each(res.differences.custom_fields, function(fieldId, field) {
                        if (field.type === 'missing')
                            addRow(field.name, '—', field.value, 'info', fieldId);
                        else if (field.type === 'conflict')
                            addRow(field.name, field.master, field.secondary, 'conflict', fieldId);
                    });

                    if (tableBody.children().length === 0)
                        tableBody.append('<tr><td colspan="4" class="text-center text-muted">No differences found.</td></tr>');

                    $('#mergePreviewContainer').removeClass('d-none');
                    $('#btnConfirm').removeClass('d-none');
                },
                error: () => showToast('Error loading comparison', 'error')
            });
        });

        // --- Confirm Merge
        $(document).on('submit', '#mergeForm', function(e) {
            e.preventDefault();
            const masterId = $('#master_contact_id').val();
            if (!masterId) {
                showToast('Please select a master contact to merge into.', 'error');
                return;
            }

            const mergeModalEl = document.getElementById('mergeModal');
            const confirmModalEl = document.getElementById('confirmMergeModal');
            const mergeModal = bootstrap.Modal.getInstance(mergeModalEl);
            if (mergeModal) mergeModal.hide();

            mergeModalEl.addEventListener('hidden.bs.modal', function handler() {
                mergeModalEl.removeEventListener('hidden.bs.modal', handler);
                new bootstrap.Modal(confirmModalEl).show();
            });

            $('#btnConfirmMergeFinal').off('click').on('click', function() {
                const btn = $(this).prop('disabled', true).text('Merging...');
                $.ajax({
                    url: "{{ route('contacts.performMerge') }}",
                    type: "POST",
                    data: $('#mergeForm').serialize(),
                    success: res => {
                        bootstrap.Modal.getInstance(confirmModalEl)?.hide();
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open').css('overflow', '');
                        showToast(res.message || 'Contacts merged successfully!', 'success');
                        $('#filterForm').trigger('submit');
                    },
                    error: xhr => {
                        showToast(xhr.responseJSON?.message || 'Merge failed', 'error');
                    },
                    complete: () => btn.prop('disabled', false).text('Confirm Merge')
                });
            });
        });

        // --- View Notes Modal
        $(document).on('click', '.btn-notes', function() {
            const notes = $(this).data('notes') || 'No notes available.';
            const modal = new bootstrap.Modal(document.getElementById('notesModal'));
            const formatted = notes.replaceAll('---- Merge on', '<hr><strong>🕓 Merge on</strong>')
                .replaceAll('→', '<span class="text-success fw-bold">→</span>');
            $('#notesContent').html(formatted);
            modal.show();
        });

        // View Merge Logs
        $(document).on('click', '.btn-logs', function() {
            const id = $(this).data('id');
            $.get(`/contacts/${id}/merge-logs`, function(html) {
                $('#mergeLogsModal').remove(); // remove old modal if exists
                $('body').append(html);
                const modal = new bootstrap.Modal(document.getElementById('mergeLogsModal'));
                modal.show();
            });
        });

    });
</script>
@endpush