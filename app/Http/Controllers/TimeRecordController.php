<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Machine;
use App\Models\TimeRecord;
use App\Models\TimeLog;
use App\Models\MachineStatus;
use Illuminate\Http\Request;
use App\Models\TimeChangeRequest;
use App\Models\Notification;
use Carbon\Carbon;
use Laravel\Pail\ValueObjects\Origin\Console;

class TimeRecordController extends Controller
{
    public function index(Request $request)
    {
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

        return view('user.time_records.index', compact('records', 'users', 'projects', 'machines'));
    }
    /**
     * Show form to create a new time record
     */
    public function create()
    {
        $users = User::all();
        $projects = Project::all();
        $machines = Machine::all();
        $statuses = MachineStatus::where('active',true)->get();

        return view('user.time_records.create', compact('users', 'projects', 'machines', 'statuses'));
    }

    /**
     * Store a new record (initial start)
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'project_id' => 'required|exists:projects,id',
            'machine_id' => 'required|exists:machines,id',
            'status_id' => 'required|exists:machine_statuses,id',
        ]);

        // Check if an open record already exists for this user/project/machine
        $existingRecord = TimeRecord::where('user_id', $request->user_id)
            ->where('project_id', $request->project_id)
            ->where('machine_id', $request->machine_id)
            ->whereNull('end_time')
            ->first();

        if ($existingRecord) {
            return redirect()->back()
                ->withErrors(['duplicate' => 'A running record already exists for this user, project, and machine.'])
                ->withInput();
        }

        $record = TimeRecord::create([
            'user_id' => $request->user_id,
            'project_id' => $request->project_id,
            'machine_id' => $request->machine_id,
            'start_time' => now(),
        ]);

        $this->changeAllOtherLogs($record->id);

        // Immediately create the first log
        TimeLog::create([
            'time_record_id' => $record->id,
            'machine_status_id' => $request->status_id,
            'start_time' => now(),
        ]);

        return redirect()->route('time-records.show', $record->id);
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
        return view('user.time_records.show', compact('record', 'statuses', 'currentLog'));
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

        return redirect()->route('time-records.list')->with('success', 'Session ended successfully.');
    }

    public function switch(Request $request, TimeLog $log)
    {
        $request->validate([
            'status_id' => 'required|exists:machine_statuses,id',
        ]);

        $this->changeAllOtherLogs($log->time_record_id);

        // Close current log
        if (is_null($log->end_time)) {
            $log->end_time = now();
            $log->save();
        }

        // Create new log
        TimeLog::create([
            'time_record_id' => $log->time_record_id,
            'machine_status_id' => $request->status_id,
            'start_time' => now(),
        ]);

        // Redirect back to the same record page
        return redirect()->route('time-records.show', $log->time_record_id)
                         ->with('success', 'Status switched successfully.');
    }

    public function changeAllOtherLogs($time_record_id)
    {
        $currentRecord = TimeRecord::find($time_record_id);
        $userId = $currentRecord->user_id;

        // get the 'ohne_aufsicht' status id (adjust as needed)
        $ohneAufsichtStatusId = MachineStatus::where('name', 'Ohne Aufsicht')->value('id');

        /**
         * Step 1: Handle other running records of this user
         */
        $otherRunningRecords = TimeRecord::where('user_id', $userId)
            ->where('id', '!=', $currentRecord->id)
            ->whereHas('logs', function ($q) {
                $q->whereNull('end_time');
            })
            ->with(['logs' => function ($q) {
                $q->whereNull('end_time');
            }])
            ->get();

        foreach ($otherRunningRecords as $record) {
            foreach ($record->logs as $runningLog) {
                // Close running log if not already "ohne_aufsicht"
                if ($runningLog->machine_status_id != $ohneAufsichtStatusId) {
                    $runningLog->end_time = now();
                    $runningLog->save();
    
                    // Create a new log for "ohne_aufsicht"
                    TimeLog::create([
                        'time_record_id' => $record->id,
                        'machine_status_id' => $ohneAufsichtStatusId,
                        'start_time' => now(),
                    ]);
                }
            }
        }
    }

    public function changeRequest($record_id)
    {
        $record = TimeRecord::with('logs.status')->findOrFail($record_id);

        return view('user.time_records.request-form', compact('record'));
    }

    public function storeChangeRequest(Request $request, $record_id)
    {
        $request->validate([
            'reason' => 'required|string|max:1000',
            'logs' => 'required|array',
        ]);

        $user_id = TimeRecord::where('id', $record_id)->first()->user_id;

        // Save to a ChangeRequest model (for approval workflow)
        TimeChangeRequest::create([
            'time_record_id' => $record_id,
            'requested_by' => $user_id,
            'reason' => $request->reason,
            'payload' => json_encode($request->logs),
            'record_start_time' => $request->record_start_time,
            'record_end_time' => $request->record_end_time,
        ]);

        $user_name = User::where('id', $user_id)->first()->user_name;

        // ✅ Create admin notification
        Notification::create([
            'user_id' => $user_id,
            'type'    => 'change_request',
            'message' => 'New time change request submitted by ' . $user_name,
            'url' => route('admin.time.change'),
        ]);

        return redirect()->route('time-records.show', $record_id)
            ->with('success', 'Change request submitted successfully.');
    }

}
