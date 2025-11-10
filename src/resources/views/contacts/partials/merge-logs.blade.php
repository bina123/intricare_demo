<div class="modal fade" id="mergeLogsModal" tabindex="-1" aria-labelledby="mergeLogsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="mergeLogsModalLabel">
                    <i class="bi bi-clock-history me-2"></i> Merge History
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                @if($logs->isEmpty())
                <p class="text-muted text-center">No merge history found for this contact.</p>
                @else
                <table class="table table-bordered table-sm align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Secondary Contact</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                {{ $log->secondary->name ?? 'N/A' }}<br>
                                <small class="text-muted">{{ $log->secondary->email ?? '' }}</small>
                            </td>
                            <td>
                                <ul class="mb-0">
                                    @if(isset($log->details['email_action']))
                                    <li><strong>Email:</strong> {{ $log->details['email_action'] }}</li>
                                    @endif
                                    @if(isset($log->details['phone_action']))
                                    <li><strong>Phone:</strong> {{ $log->details['phone_action'] }}</li>
                                    @endif
                                    @if(!empty($log->details['files']))
                                    @foreach($log->details['files'] as $file => $msg)
                                    <li><strong>{{ ucfirst(str_replace('_',' ',$file)) }}:</strong> {{ $msg }}</li>
                                    @endforeach
                                    @endif
                                    @if(!empty($log->details['custom_fields']))
                                    <li>
                                        <strong>Custom Fields:</strong>
                                        <ul>
                                            @foreach($log->details['custom_fields'] as $cf)
                                            <li>{{ $cf }}</li>
                                            @endforeach
                                        </ul>
                                    </li>
                                    @endif
                                </ul>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>