<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TimeChangeRequest;
use Illuminate\Http\Request;
use App\Traits\HandleMachineLogs;
use App\Models\TimeRecord;
use App\Models\User;
Use App\Models\Project;
use App\Models\Machine;
use App\Models\MachineStatus;
use App\Models\TimeLog;
use Carbon\Carbon;

class TimeController extends Controller
{
    use HandleMachineLogs;
    public function records(Request $request) {
        $query = TimeRecord::with(['user', 'project', 'machine']);

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('machine_id')) {
            $query->where('machine_id', $request->machine_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('end_time');
            } elseif ($request->status === 'ended') {
                $query->whereNotNull('end_time');
            }
        }

        if ($request->filled('date')) {
            $query->whereDate('start_time', $request->date);
        }

        // Pagination
        $records = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // For filter dropdowns
        $users = User::all();
        $projects = Project::all();
        $machines = Machine::all();

        return view('admin.time.list', compact('records', 'users', 'projects', 'machines'));
    }

    public function editRecord(Request $request, $id) {
        
        $record = TimeRecord::findOrFail($id);

        // Load dropdown data
        $users = User::all();
        $projects = Project::all();
        $machines = Machine::all();

        return view('admin.time.record-edit', compact('record', 'users', 'projects', 'machines'));
    }

    public function updateRecord(Request $request, $id) {
        
        $record = TimeRecord::findOrFail($id);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'start_time'=> 'required|date',
            'end_time'=> 'nullable|date|after_or_equal:start_time',
        ]);

        $record->update([
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
            'machine_id' => $request->machine_id,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return redirect()
            ->route('admin.time.records')
            ->with('success', 'Time record updated successfully.');
    }

    public function deleteRecord($id) {
        TimeRecord::findOrFail($id)->delete();
        return back()->with('success', 'Machine status deleted successfully.');
    }

    public function changeTimeLogs($id) {
        $record = TimeRecord::with('logs.status')->findOrFail($id);

        return view('admin.time.change-logs', compact('record'));
    }

    public function storeAndApproveLogs(Request $request, $id)
    {
        $request->validate([
            'logs' => 'required|array',
            'reason' => 'nullable|string|max:1000',
        ]);

        $adminId = auth()->id();

        // 1️⃣ Create the change request
        $changeRequest = TimeChangeRequest::create([
            'time_record_id' => $id,
            'requested_by' => $adminId,
            'reason' => $request->reason ?? 'Direct admin change',
            'payload' => json_encode($request->logs),
            'status' => 'accepted',          // mark as already accepted
            'approved_by' => $adminId,
            'approved_at' => now(),
            'record_start_time' => $request->record_start_time,
            'record_end_time' => $request->record_end_time,
        ]);

        // 2️⃣ Apply the logs immediately
        $payload = json_decode($changeRequest->payload, true);

        if (is_array($payload)) {
            foreach ($payload as $logData) {
                // Update existing log
                if (!empty($logData['id'])) {
                    $log = TimeLog::find($logData['id']);
                    if ($log) {
                        if (!empty($logData['delete']) && $logData['delete'] === 'true') {
                            $log->delete();
                        } else {
                            $log->update([
                                'start_time' => $logData['start_time'] ?? $log->start_time,
                                'end_time'   => $logData['end_time'] ?? $log->end_time,
                                'machine_status_id' => $logData['status_id'] ?? $log->machine_status_id,
                            ]);
                        }
                    }
                } 
                // Create new log
                else {
                    TimeLog::create([
                        'time_record_id' => $id,
                        'start_time' => $logData['start_time'] ?? null,
                        'end_time' => $logData['end_time'] ?? null,
                        'machine_status_id' => $logData['status_id'] ?? null,
                    ]);
                }
            }
        }

        if (!empty($request->record_start_time) || !empty($request->record_end_time)) {
            $record = TimeRecord::find($id);
            if ($record) {
                $record->update([
                    'start_time'=> $request->record_start_time,
                    'end_time'=> $request->record_end_time,
                ]);
            }
        }

        return redirect()->back()->with('success', 'Changes applied and recorded successfully.');
    }

    public function show($id)
    {
        // Load record with relationships
        $record = TimeRecord::with(['user', 'project', 'machine', 'logs.status'])->findOrFail($id);

        // Get all statuses for the status-switching buttons
        $statuses = MachineStatus::where('active',true)->get();

        // Find current log (the one still open)
        $currentLog = $record->logs()->whereNull('end_time')->latest()->first();

        // Pass to the view
        return view('admin.time.show', compact('record', 'statuses', 'currentLog'));
    }

    public function end($id)
    {
        $record = TimeRecord::with('logs')->findOrFail($id);

        // 1️⃣ Close any open log
        $activeLog = $record->logs()->whereNull('end_time')->latest()->first();
        if ($activeLog) {
            $activeLog->end_time = now();
            $activeLog->save();
        }

        // 2️⃣ Close the record
        $record->end_time = now();
        $record->save();

        return redirect()->route('admin.time.records')->with('success', 'Session ended successfully.');
    }

    public function switch(Request $request, TimeLog $log)
    {
        $request->validate([
            'status_id' => 'required|exists:machine_statuses,id',
        ]);

        // Close current log
        if (is_null($log->end_time)) {
            $log->end_time = now();
            $log->save();
        }

        // Create new log
        $newLog = TimeLog::create([
            'time_record_id' => $log->time_record_id,
            'machine_status_id' => $request->status_id,
            'start_time' => now(),
        ]);

        // Redirect back to the same record page
        return redirect()->route('admin.time.show', $log->time_record_id)
                         ->with('success', 'Status switched successfully.');
    }

    public function compare(Request $request) {

        $comparison = [];
        $totalRustzeit = 0;
        $totalMitAufsicht = 0;
        $totalOhneAufsicht = 0;
        $totalNachtZeit = 0;
        $totalMachineTime = 0;


        // Filters
        $query = Project::where('from_machine_logs', 1);
        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }
        $projects = $query->get();

        if ($request->filled('date')) {
            $filterDate = Carbon::parse($request->date)->toDateString();

            // Get time records overlapping the date
            $records = TimeRecord::with(['logs.status', 'project.processes'])
                ->when($request->filled('date'), function($q) use ($filterDate) {
                    $q->whereDate('start_time', '<=', $filterDate)
                    ->where(function($q2) use ($filterDate) {
                        $q2->whereNull('end_time')
                            ->orWhereDate('end_time', '>=', $filterDate);
                    });
                })
                ->when($request->filled('project_id'), function($q) use ($request) {
                    $q->where('project_id', $request->project_id);
                })
                ->get();

            foreach ($records as $record) {

                $recordStart = Carbon::parse($record->start_time);
                $recordEnd = $record->end_time ? Carbon::parse($record->end_time) : Carbon::now();

                $totalRustzeit = 0;
                $totalMitAufsicht = 0;
                $totalOhneAufsicht = 0;
                $totalNachtZeit = 0;
                $processesForRecord = [];

                foreach ($record->logs as $log) {
                    $logStart = Carbon::parse($log->start_time);
                    $logEnd = $log->end_time ? Carbon::parse($log->end_time) : Carbon::now();
                    $duration = $logStart->diffInSeconds($logEnd);

                    switch ($log->status->name) {
                        case 'Rustzeit': $totalRustzeit += $duration; break;
                        case 'Mit Aufsicht': $totalMitAufsicht += $duration; break;
                        case 'Ohne Aufsicht': $totalOhneAufsicht += $duration; break;
                        case 'Nacht Zeit': $totalNachtZeit += $duration; break;
                    }
                }

                // 2️⃣ Machine time totals
                $totalMachineTime = 0;

                foreach ($record->project->processes as $process) {
                    $processStart = Carbon::parse($process->start_time);
                    $processEnd = Carbon::parse($process->end_time);

                    // Calculate overlap with record
                    $overlapStart = $processStart->lt($recordStart) ? $recordStart : $processStart;
                    $overlapEnd = $processEnd->gt($recordEnd) ? $recordEnd : $processEnd;

                    if ($overlapEnd->gt($overlapStart)) {
                        $totalMachineTime += $overlapStart->diffInSeconds($overlapEnd);

                        // Extra time outside record counted as ohne Aufsicht
                        if ($processEnd->gt($recordEnd) && $processStart->between($recordStart, $recordEnd)) {
                            $extra = $recordEnd->diffInSeconds($processEnd);
                            $totalOhneAufsicht += $extra;
                        }

                        // ✅ Collect process details for view
                        $processesForRecord[] = [
                            'process_name' => $process->name ?? 'N/A',
                            'procedure_name' => $process->procedure->name ?? 'N/A',
                            'bauteil_name' => $process->bauteil->name ?? 'N/A',
                            'start_time' => $processStart->format('Y-m-d H:i:s'),
                            'end_time' => $processEnd->format('Y-m-d H:i:s'),
                        ];
                    }
                }

                $comparison[] = [
                    'record' => $record,
                    'rustzeit' => gmdate('H:i:s', $totalRustzeit),
                    'mit_aufsicht' => gmdate('H:i:s', $totalMitAufsicht),
                    'ohne_aufsicht' => gmdate('H:i:s', $totalOhneAufsicht),
                    'nacht_zeit' => gmdate('H:i:s', $totalNachtZeit),
                    'machine_time' => gmdate('H:i:s', $totalMachineTime),
                    'processes' => $processesForRecord,
                ];
            }
        }

        return view('admin.time.compare', compact('comparison', 'projects'));
    }

    public function machineLogs(Request $request)
    {
        $data = $this->getMachineLogs($request);
        return view('admin.time.logs', $data);
    }

    public function parseLog()
    {
        $file = storage_path('app/public/logs/LOGFILE.OLD');
        return $this->parseMachineLogs($file);
    }

    public function change(Request $request)
    {
        $pendingRequests = TimeChangeRequest::with(['timeRecord.project', 'timeRecord.machine'])->whereNull('status')->latest()->get();
        $processedRequests = TimeChangeRequest::with(['timeRecord.project', 'timeRecord.machine'])->whereNotNull('status')->latest()->get();

        return view('admin.time.change', compact('pendingRequests', 'processedRequests'));
    }

    public function acceptChange($id)
    {
        $changeRequest = TimeChangeRequest::findOrFail($id);

        // Decode payload into PHP array
        $payload = json_decode($changeRequest->payload, true);

        if (is_array($payload)) {
            foreach ($payload as $logData) {
                // Update existing log
                if (!empty($logData['id'])) {
                    $log = TimeLog::find($logData['id']);
                    if ($log) {
                        if(!empty($logData['delete']) && $logData['delete'] === 'true'){
                            $log->delete();
                        } else {
                            $log->update([
                                'start_time' => $logData['start_time'] ?? $log->start_time,
                                'end_time'   => $logData['end_time'] ?? $log->end_time,
                                'machine_status_id'  => $logData['status_id'] ?? $log->machine_status_id,
                            ]);
                        }
                    }
                } 
                // Create new log
                else {
                    TimeLog::create([
                        'time_record_id' => $changeRequest->time_record_id,
                        'start_time'     => $logData['start_time'] ?? null,
                        'end_time'       => $logData['end_time'] ?? null,
                        'machine_status_id'      => $logData['status_id'] ?? null,
                    ]);
                }
            }
        }

        if (!empty($changeRequest->record_start_time) || !empty($changeRequest->record_end_time)) {
            $record = TimeRecord::find($id);
            if ($record) {
                $record->update([
                    'start_time'=> $changeRequest->record_start_time,
                    'end_time'=> $changeRequest->record_end_time,
                ]);
            }
        }

        $changeRequest->update([
            'status' => 'accepted',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Change request accepted successfully.');
    }

    public function rejectChange($id)
    {
        $changeRequest = TimeChangeRequest::findOrFail($id);
        $changeRequest->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return redirect()->back()->with('error', 'Change request rejected.');
    }
}
