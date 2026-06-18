<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Machine;
use App\Models\TimeRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ActivityTimelineController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display the activity timeline page
     */
    public function index()
    {
        $endDate = Carbon::today();
        $startDate = $endDate->copy()->subDays(9); // Last 10 days

        // 2. Fetch the raw data array directly
        $activityData = $this->fetchTimelineData($startDate, $endDate);

        return view('admin.activity-timeline.index', compact(
            'activityData',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get activity data for machines and users (AJAX endpoint)
     */
    public function getActivityData(Request $request = null)
    {
        if ($request !== null) {
            $startDate = $request->filled('start_date') 
                ? Carbon::createFromFormat('Y-m-d', $request->start_date)
                : Carbon::today()->subDays(9);
            
            $endDate = $request->filled('end_date')
                ? Carbon::createFromFormat('Y-m-d', $request->end_date)
                : Carbon::today();
        } else {
            $startDate = Carbon::today()->subDays(9);
            $endDate = Carbon::today();
        }

        // Get machine activity
        $machineActivity = $this->getMachineActivityByDay($startDate, $endDate);
        
        // Get user activity
        $userActivity = $this->getUserActivityByDay($startDate, $endDate);

        // Get detailed activity records for table
        $detailedActivity = $this->getDetailedActivity($startDate, $endDate);

        return response()->json([
            'machineActivity' => $machineActivity,
            'userActivity' => $userActivity,
            'detailedActivity' => $detailedActivity,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d'),
        ]);
    }

    /**
     * Shared Internal Logic to Fetch and Process Timeline Data
     */
    protected function fetchTimelineData(Carbon $startDate, Carbon $endDate)
    {
        // Use fresh, isolated copies of dates to protect calculations
        $start = $startDate->copy()->startOfDay();
        $end = $endDate->copy()->endOfDay();

        $machineActivity = $this->getMachineActivityByDay($start, $end);
        $userActivity = $this->getUserActivityByDay($start, $end);
        $detailedActivity = $this->getDetailedActivity($start, $end);

        return [
            'machineActivity' => $machineActivity,
            'userActivity' => $userActivity,
            'detailedActivity' => $detailedActivity,
            'startDate' => $start->format('Y-m-d'),
            'endDate' => $end->format('Y-m-d'),
        ];
    }

    /**
     * Aggregate machine activity by day for last N days
     * Returns: [date => [machine_name => hours, ...], ...]
     */
    private function getMachineActivityByDay($startDate, $endDate)
    {
        $records = TimeRecord::with(['machine', 'project', 'position'])
            ->whereBetween('start_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->where('machine_id', '!=', null)
            ->get();

        $activity = [];

        // Initialize all dates in range
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $activity[$current->format('Y-m-d')] = [];
            $current->addDay();
        }

        // Aggregate hours by machine and date
        foreach ($records as $record) {
            $date = Carbon::parse($record->start_time)->format('Y-m-d');
            $machineName = $record->machine?->name ?? 'Unknown Machine';
            
            // Calculate duration in hours
            $duration = $this->calculateDuration($record->start_time, $record->end_time);

            if (!isset($activity[$date][$machineName])) {
                $activity[$date][$machineName] = 0;
            }
            $activity[$date][$machineName] += $duration;
        }

        // Format for chart
        $chartData = [];
        foreach ($activity as $date => $machines) {
            foreach ($machines as $machine => $hours) {
                $chartData[] = [
                    'date' => $date,
                    'machine' => $machine,
                    'hours' => round($hours, 2),
                ];
            }
        }

        return [
            'byDay' => $activity,
            'chartData' => $chartData,
        ];
    }

    /**
     * Aggregate user activity by day for last N days
     * Returns: [date => [user_name => hours, ...], ...]
     */
    private function getUserActivityByDay($startDate, $endDate)
    {
        $records = TimeRecord::with(['user', 'project', 'position'])
            ->whereBetween('start_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->whereNotNull('user_id')
            ->get();

        $activity = [];

        // Initialize all dates in range
        $current = $startDate->copy();
        while ($current <= $endDate) {
            $activity[$current->format('Y-m-d')] = [];
            $current->addDay();
        }

        // Aggregate hours by user and date
        foreach ($records as $record) {
            $date = Carbon::parse($record->start_time)->format('Y-m-d');
            $userName = $record->user?->name ?? 'Unknown User';
            
            // Calculate duration in hours
            $duration = $this->calculateDuration($record->start_time, $record->end_time);

            if (!isset($activity[$date][$userName])) {
                $activity[$date][$userName] = 0;
            }
            $activity[$date][$userName] += $duration;
        }

        // Format for chart
        $chartData = [];
        foreach ($activity as $date => $users) {
            foreach ($users as $user => $hours) {
                $chartData[] = [
                    'date' => $date,
                    'user' => $user,
                    'hours' => round($hours, 2),
                ];
            }
        }

        return [
            'byDay' => $activity,
            'chartData' => $chartData,
        ];
    }

    /**
     * Get detailed activity records for table display
     */
    private function getDetailedActivity($startDate, $endDate)
    {
        return TimeRecord::with(['user', 'machine', 'project', 'position'])
            ->whereBetween('start_time', [$startDate->startOfDay(), $endDate->endOfDay()])
            ->orderBy('start_time', 'desc')
            ->get()
            ->map(function ($record) {
                return [
                    'id' => $record->id,
                    'date' => Carbon::parse($record->start_time)->format('Y-m-d'),
                    'user' => $record->user?->name ?? 'N/A',
                    'machine' => $record->machine?->name ?? 'N/A',
                    'project' => $record->project?->project_name ?? 'N/A',
                    'position' => $record->position?->name ?? 'N/A',
                    'duration' => round($this->calculateDuration($record->start_time, $record->end_time), 2),
                    'start_time' => $record->start_time,
                    'end_time' => $record->end_time,
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Calculate duration in hours between two timestamps
     */
    private function calculateDuration($startTime, $endTime)
    {
        if (!$endTime) {
            return 0;
        }

        $start = Carbon::parse($startTime);
        $end = Carbon::parse($endTime);

        return $start->diffInMinutes($end) / 60;
    }
}
