<?php

namespace App\Traits;
use App\Models\Project;
use Carbon\Carbon;

trait HandleMachineLogs
{
    public function getMachineLogs($request) {
        $query = Project::with(['procedures.processes', 'processes', 'bauteile.processes'])
            ->where(function ($q) {
                $q->whereHas('processes')
                ->orWhereHas('procedures.processes')
                ->orWhereHas('bauteile.processes');
            });

        $query->where('from_machine_logs', 1);

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

        // Paginated projects
        $projects = $query->paginate(5)->withQueryString();

        // Filter out empty relations
        $projects->each(function ($project) {
            $project->bauteile = $project->bauteile->filter(fn($b) => $b->processes->isNotEmpty());
            $project->procedures = $project->procedures->filter(fn($p) => $p->processes->isNotEmpty());
        });
        $allProjects = Project::orderBy('project_name')
            ->where(function ($q) {
                $q->whereHas('processes')
                ->orWhereHas('procedures.processes')
                ->orWhereHas('bauteile.processes');
            })
            ->get();

        // 🔁 Return as array so you can destructure in controller
        return compact('projects', 'allProjects');
    }

    public function parseMachineLogs($logFile = null) {

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
