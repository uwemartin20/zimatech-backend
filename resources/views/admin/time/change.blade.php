@extends('admin.layouts.index')

@section('content')
<div class="container mt-4">

    {{-- ======================
        Pending Requests
    ======================= --}}
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Ausstehende Nachtragsanträge</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Projekt</th>
                            <th>Position</th>
                            <th>Maschine</th>
                            <th>Bediener</th>
                            <th>Start</th>
                            <th>Ende</th>
                            <th>Grund</th>
                            <th>Payload</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingRequests as $index => $record)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $record->timeRecord->project->project_name ?? 'N/A' }}
                                    <small class="text-muted d-block project-auftrag">
                                        {{ "ZF: ".$record->timeRecord->project->auftragsnummer_zf ." ZT: " .$record->timeRecord->project->auftragsnummer_zt }}
                                    </small>
                                </td>
                                <td>{{ $record->timeRecord->position->name }}</td>
                                <td>{{ $record->timeRecord->machine->name ?? 'N/A' }}</td>
                                <td>{{ $record->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ $record->timeRecord->start_time ?? 'N/A' }}</td>
                                <td>{{ $record->timeRecord->end_time ?? 'N/A' }}</td>
                                <td>{{ $record->reason }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary toggle-details" type="button"
                                        data-bs-toggle="collapse" data-target="#details{{ $record->id }}">
                                        <i class="bi bi-eye"></i> Änderungen anzeigen
                                    </button>                              
                                <td>
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('admin.time.change.accept', $record->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-success btn-sm" type="submit">
                                                <i class="bi bi-check-circle"></i> Übernehmen
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.time.change.reject', $record->id) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-danger btn-sm" type="submit">
                                                <i class="bi bi-x-circle"></i> Ablehnen
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            {{-- Hidden expandable row --}}
                            <tr id="details{{ $record->id }}" class="details-row" style="display:none;">
                                <td colspan="9">
                                    @php
                                        $payloadLogs = json_decode($record->payload, true);
                                        $originalLogs = $record->timeRecord?->logs ?? collect();
                                    @endphp

                                    <div class="p-3 border rounded bg-light">
                                        <div class="row g-3">
                                            {{-- Original Logs --}}
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-danger mb-2">
                                                    <i class="bi bi-clock-history"></i> Originalprotokolle
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-danger text-center">
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Status</th>
                                                                <th>Start</th>
                                                                <th>Ende</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @forelse($originalLogs as $index => $log)
                                                                <tr class="bg-danger-subtle text-dark">
                                                                    <td>{{ $log->status->id }}</td>
                                                                    <td>{{ $log->status->name ?? 'N/A' }}</td>
                                                                    <td>{{ \Carbon\Carbon::parse($log->start_time)->format('Y-m-d H:i') }}</td>
                                                                    <td>{{ $log->end_time ? \Carbon\Carbon::parse($log->end_time)->format('Y-m-d H:i') : 'Running' }}</td>
                                                                </tr>
                                                            @empty
                                                                <tr>
                                                                    <td colspan="4" class="text-center text-muted">Es wurden keine Originalprotokolle gefunden.</td>
                                                                </tr>
                                                            @endforelse
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>

                                            {{-- Requested Changes --}}
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-success mb-2">
                                                    <i class="bi bi-arrow-repeat"></i> Gewünschte Änderungen
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-success text-center">
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Status</th>
                                                                <th>Start</th>
                                                                <th>End</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($payloadLogs as $index => $log)
                                                                @php
                                                                    if (!empty($log['status_id'])) {
                                                                        $machine_status = App\Models\MachineStatus::find($log['status_id']);
                                                                    } else {
                                                                        $machine_status = null;
                                                                    }
                                                                @endphp
                                                                <tr class="bg-success-subtle text-dark">
                                                                    <td>{{ $log['id'] ?? 'New' }}</td>
                                                                    <td>{{ $machine_status ? $machine_status->name : '-' }}</td>
                                                                    <td>{{ $log['start_time'] ? \Carbon\Carbon::parse($log['start_time'])->format('Y-m-d H:i') : '-' }}</td>
                                                                    <td>{{ isset($log['end_time']) && $log['end_time'] ? \Carbon\Carbon::parse($log['end_time'])->format('Y-m-d H:i') : 'Running' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    Es wurden keine ausstehenden Anfragen gefunden.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ======================
        Processed Requests
    ======================= --}}
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0">Zuvor bearbeitete Anfragen</h5>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Projekt</th>
                            <th>Position</th>
                            <th>Maschine</th>
                            <th>Bediener</th>
                            <th>Start</th>
                            <th>Ende</th>
                            <th>Grund</th>
                            <th>Payload</th>
                            <th>Status</th>
                            <th>Rezension von</th>
                            <th>Rezension am</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($processedRequests as $index => $record)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>
                                    {{ $record->timeRecord->project->project_name ?? 'N/A' }}
                                    <small class="text-muted d-block project-auftrag">
                                        {{ "ZF: ".$record->timeRecord->project->auftragsnummer_zf ." ZT: " .$record->timeRecord->project->auftragsnummer_zt }}
                                    </small>
                                </td>
                                <td>{{ $record->timeRecord->position->name }}</td>
                                <td>{{ $record->timeRecord->machine->name ?? 'N/A' }}</td>
                                <td>{{ $record->requestedBy->name ?? 'N/A' }}</td>
                                <td>{{ $record->timeRecord->start_time ?? 'N/A' }}</td>
                                <td>{{ $record->timeRecord->end_time ?? 'N/A' }}</td>
                                <td>{{ $record->reason }}</td>
                                <td>
                                    <button class="btn btn-sm btn-outline-secondary toggle-details" type="button"
                                        data-bs-toggle="collapse" data-target="#details{{ $record->id }}">
                                        <i class="bi bi-eye"></i> Änderungen anzeigen
                                    </button>
                                </td>
                                <td>
                                    @if($record->status === 'accepted')
                                        <span class="badge bg-success">Übernimmt</span>
                                    @elseif($record->status === 'rejected')
                                        <span class="badge bg-danger">Abgelehnt</span>
                                    @endif
                                </td>
                                <td>{{ $record->approvedBy->name ?? '-' }}</td>
                                <td>{{ $record->approved_at ? $record->approved_at : '' }}</td>
                            </tr>
                            {{-- Hidden expandable row --}}
                            <tr id="details{{ $record->id }}" class="details-row" style="display:none;">
                                <td colspan="11">
                                    @php
                                        $payloadLogs = json_decode($record->payload, true);
                                    @endphp

                                    <div class="p-3 border rounded bg-light">
                                        <div class="row g-3">

                                            {{-- Requested Changes --}}
                                            <div class="col-md-6">
                                                <h6 class="fw-bold text-success mb-2">
                                                    <i class="bi bi-arrow-repeat"></i> Gewünschte Änderungen
                                                </h6>
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-success text-center">
                                                            <tr>
                                                                <th>ID</th>
                                                                <th>Status</th>
                                                                <th>Start</th>
                                                                <th>End</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($payloadLogs as $index => $log)
                                                                @php
                                                                    if (!empty($log['status_id'])) {
                                                                        $machine_status = App\Models\MachineStatus::find($log['status_id']);
                                                                    } else {
                                                                        $machine_status = null;
                                                                    }
                                                                @endphp
                                                                <tr class="bg-success-subtle text-dark">
                                                                    <td>{{ $log['id'] ?? 'New' }}</td>
                                                                    <td>{{ $machine_status ? $machine_status->name : '-' }}</td>
                                                                    <td>{{ $log['start_time'] ? \Carbon\Carbon::parse($log['start_time'])->format('Y-m-d H:i') : '-' }}</td>
                                                                    <td>{{ isset($log['end_time']) && $log['end_time'] ? \Carbon\Carbon::parse($log['end_time'])->format('Y-m-d H:i') : 'Running' }}</td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center text-muted py-4">
                                    Bisher wurden noch keine Anfragen bearbeitet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-details').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.dataset.target;
                const row = document.querySelector(targetId);
    
                // Close other open rows
                document.querySelectorAll('.details-row').forEach(r => {
                    if (r !== row) r.style.display = 'none';
                });
    
                // Toggle this row
                row.style.display = (row.style.display === 'none' || row.style.display === '') 
                    ? 'table-row' 
                    : 'none';
            });
        });
    });
</script>    
@endsection
