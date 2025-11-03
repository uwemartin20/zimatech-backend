<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ProjectController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // 
    }

    public function index()
    {
        $projects = Project::all();

        return view("user.projects.index", compact("projects"));

    }

    public function projectLogs(Request $request)
    {
        $query = Project::with(['procedures.processes', 'processes', 'bauteile.processes']);

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }

        // ✅ Filter by calendar week
        if ($request->filled('week')) {
            // week format = "2025-W04"
            [$year, $week] = explode('-W', $request->week);

            $startOfWeek = Carbon::now()->setISODate($year, $week)->startOfWeek();
            $endOfWeek = Carbon::now()->setISODate($year, $week)->endOfWeek();

            $query->where(function($q) use ($startOfWeek, $endOfWeek) {
                $q->whereHas('processes', function($q2) use ($startOfWeek, $endOfWeek) {
                    $q2->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                    ->orWhereBetween('end_time', [$startOfWeek, $endOfWeek]);
                })
                ->orWhereHas('procedures.processes', function($q3) use ($startOfWeek, $endOfWeek) {
                    $q3->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                    ->orWhereBetween('end_time', [$startOfWeek, $endOfWeek]);
                })
                ->orWhereHas('bauteile.processes', function($q4) use ($startOfWeek, $endOfWeek) {
                    $q4->whereBetween('start_time', [$startOfWeek, $endOfWeek])
                    ->orWhereBetween('end_time', [$startOfWeek, $endOfWeek]);
                });
            });
        }

        // ✅ Filter by single day
        elseif ($request->filled('day')) {
            $day = Carbon::parse($request->day)->toDateString();

            $query = Project::with([
                'processes' => function($q) use ($day) {
                    $q->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                },
                'procedures.processes' => function($q) use ($day) {
                    $q->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                },
                'bauteile.processes' => function($q) use ($day) {
                    $q->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                }
            ])
            ->where(function($q) use ($day) {
                $q->whereHas('processes', function($q2) use ($day) {
                    $q2->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                })
                ->orWhereHas('procedures.processes', function($q3) use ($day) {
                    $q3->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                })
                ->orWhereHas('bauteile.processes', function($q4) use ($day) {
                    $q4->whereDate('start_time', $day)
                    ->orWhereDate('end_time', $day);
                });
            });
        }

        $projects = $query->paginate(5)->withQueryString();
        $projects->each(function ($project) {
            $project->bauteile = $project->bauteile->filter(fn($b) => $b->processes->isNotEmpty());
            $project->procedures = $project->procedures->filter(fn($p) => $p->processes->isNotEmpty());
        });
        $allProjects = Project::orderBy('project_name')->get();

        return view('user.projects.index', compact('projects', 'allProjects'));
    }

    public function parseLog()
    {
        $logFile = storage_path('app/public/logs/LOGFILE.OLD');

        if (!file_exists($logFile)) {
            $sourceFile = "Y:/LOGFILE.OLD";
            copy($sourceFile, $logFile);
        }

        $cmd = "php artisan parse:drilllog \"$logFile\"";

        $descriptors = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($cmd, $descriptors, $pipes, base_path());

        $output = stream_get_contents($pipes[1]);
        $error = stream_get_contents($pipes[2]);

        foreach ($pipes as $pipe) { fclose($pipe); }

        $status = proc_close($process);

        if ($status !== 0) {
            return response()->json([
                'status' => 'error',
                'message' => $error ?: 'Unknown error',
            ]);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Log parsed successfully!',
        ]);
    }

}