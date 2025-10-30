<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with(['procedures.processes'])
            ->orderBy('id','desc')
            ->paginate(20);
        return response()->json($projects);
    }

    public function show($id)
    {
        $project = Project::with(['procedures.processes', 'programRuns'])->findOrFail($id);
        return response()->json($project);
    }

    public function showTable(Request $request)
    {
        $query = Project::with(['procedures.processes', 'processes', 'bauteile.processes']);

        if ($request->filled('project_id')) {
            $query->where('id', $request->project_id);
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $start = $request->start_date;
            $end = $request->end_date;

            $query->where(function($q) use ($start, $end) {
                // Filter direct processes
                $q->whereHas('processes', function($q2) use ($start, $end) {
                    $q2->whereBetween('start_time', [$start, $end]);
                })
                // Filter processes inside procedures
                ->orWhereHas('procedures.processes', function($q3) use ($start, $end) {
                    $q3->whereBetween('start_time', [$start, $end]);
                })
                // Filter processes inside bauteile
                ->orWhereHas('bauteile.processes', function($q4) use ($start, $end) {
                    $q4->whereBetween('start_time', [$start, $end]);
                });
            });
        }

        $projects = $query->paginate(2)->withQueryString();
        $allProjects = Project::orderBy('project_name')->get();

        return view('projects.table', compact('projects', 'allProjects'));
    }

    public function parseLog()
    {
        $logFile = storage_path('app/public/logs/LOGFILE.OLD');

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
