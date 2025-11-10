<div class="modal fade" id="mergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <form id="mergeForm">
                @csrf
                <input type="hidden" name="secondary_id" id="secondary_id" value="{{ $secondary->id }}">

                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Merge Contact: {{ $secondary->name }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p class="mb-2">
                        <strong>Secondary Contact:</strong> {{ $secondary->name }} ({{ $secondary->email }})
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Master Contact (Keep)</label>
                        <select id="master_contact_id" name="master_contact_id" class="form-select">
                            <option value="">Select Master Contact</option>
                            @foreach($contacts as $contact)
                            <option value="{{ $contact->id }}">{{ $contact->name }} ({{ $contact->email }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="mergePreviewContainer" class="d-none mt-4">
                        <h6 class="fw-bold mb-2">Preview & Choose Merge Policy</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th width="25%">Field</th>
                                        <th width="25%" class="text-success">Master (Keep)</th>
                                        <th width="25%" class="text-danger">Secondary (Merge)</th>
                                        <th width="25%" class="text-center">Decision</th>
                                    </tr>
                                </thead>
                                <tbody id="mergePreviewBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="btnConfirm" class="btn btn-success d-none">Confirm Merge</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- Final Confirmation Modal -->
<div class="modal fade" id="confirmMergeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-semibold">Confirm Merge</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-0">
                    Are you sure you want to merge these contacts?<br>
                    <strong>This action cannot be undone.</strong><br>
                    The secondary contact will be marked as merged and all differences will be applied to the master contact.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btnConfirmMergeFinal" class="btn btn-danger">Yes, Merge</button>
            </div>
        </div>
    </div>
</div>