<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Procedure;
use App\Models\Process;
use App\Models\Bauteil;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ParseDrillLog extends Command
{
    protected $signature = 'parse:drilllog {path}';
    protected $description = 'Parse drilling machine log and store data in database';

    public function handle()
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return 1;
        }

        $processedDir = dirname($path) . '/processed/';
        $filename = basename($path);

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->info("Loaded " . count($lines) . " lines.");

        $logDate = null;
        $currentProject = null;
        $currentProcedure = null;
        $currentProcName = null;
        $processMap = [];
        $counter = 0;

        // For direct runs (outside procedure)
        $standaloneProc = null;
        $standaloneStart = null;
        $standaloneName = null;

        foreach ($lines as $i => $line) {
            $ln = trim($line);

            // --- STEP 1: Detect log date ---
            if (preg_match('/<<\s*(\d{1,2}-[A-Za-z]{3}-\d{4})\s*>>/', $ln, $m)) {
                $logDate = $m[1];
                $this->info("📅 Detected log date: $logDate");
                continue;
            }

            // Skip any lines before log date
            if (!$logDate) continue;

            // --- STEP 2: START DER PROZEDUR ---
            if (preg_match('/START DER PROZEDUR.*\((.*?)\)/i', $ln, $m)) {
                $pathInside = $m[1];
                $parts = preg_split('/[\\\\\/]+/', $pathInside);
                if (count($parts) >= 2) {
                    $projectName = $parts[count($parts) - 2];
                    $auftragsnummer =  explode('_', $projectName)[0];
                    $projectName = Str::after($projectName, '_');
                } else {
                    $projectName = null;
                    $auftragsnummer =  null;
                }

                if (preg_match('/\b(\d{2}:\d{2}:\d{2})\b/', $ln, $t)) {
                    // Combine log date with time
                    $startTime = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$t[1]}")->toDateTimeString();
                } else {
                    $startTime = null;
                }

                // find or create project
                $currentProject = Project::firstOrCreate(
                    ['auftragsnummer' => $auftragsnummer, 'project_name' => $projectName]
                );

                // store previous procedure if any
                if ($currentProcedure) {
                    $this->storeProcedure($currentProject, $currentProcedure, $processMap);
                }

                // start new procedure
                $currentProcedure = [
                    'start_time' => $startTime,
                    'end_time' => null,
                    'source_file' => $pathInside,
                ];
                $processMap = [];
                $currentProcName = null;

                $this->info("## STARTED Procedure for: {$projectName} ({$auftragsnummer})");
                continue;
            }

            // --- STEP 3: Process start (TO CNC...) ---
            if (preg_match('/TO CNC:([^\\)]+?)(?:\.fid|\s|\))/i', $ln, $m)) {
                $procName = trim($m[1]);
                $procName = str_replace(['\\', '/'], '_', $procName);
                $currentProcName = $procName;
                
                
                // Extract full path after "FROM IPC:"
                if (preg_match('/FROM IPC:([^\s]+)/i', $line, $matches)) {
                    $fullPath = $matches[1]; // full path
                    $sourceFile = mb_convert_encoding($fullPath, 'UTF-8', 'auto');

                    // Split by backslash
                    $parts = preg_split('/\\\\/', $fullPath);

                    $projectName = null;
                    $auftragsnummer = null;
                    $bauteile = [];
                    if (($parts[2] == "NULLEN.FID") || (stripos($parts[count($parts)-1], '.fid') === false)) continue;
                    
                    foreach ($parts as $index => $part) {
                        if (
                            !$projectName &&
                            preg_match('/^\d+[-_].+$/', $part) &&
                            !preg_match('/\.fid$/i', $part)
                        ) {
                            // First folder that starts with number + underscore → project
                            $projectName = $part;
                            $auftragsnummer =  explode('_', $projectName)[0];
                            $projectName = Str::after($projectName, '_');
                        } elseif ($projectName) {
                            // Everything after project **except last element** (the .fid file) → bauteile
                            if ($index < count($parts) - 1) {
                                $bauteile[] = $part;
                            }
                        }
                    }
                    // find or create project
                    $currentProject = Project::firstOrCreate(
                        ['auftragsnummer' => $auftragsnummer, 'project_name' => $projectName]
                    );

                    $parentId = null; // No parent initially

                    foreach ($bauteile as $bauteilName) {
                        $bauteil = Bauteil::firstOrCreate(
                            [
                                'name' => $bauteilName,
                                'project_id' => $currentProject->id,
                                'parent_id' => $parentId
                            ]
                        );

                        // Next bauteil’s parent is the current one
                        $parentId = $bauteil->id;
                    }

                    $this->info("Found START for project: {$projectName} ({$auftragsnummer})");
                }

                // combine time from log line
                $timeMatch = preg_match('/\b(\d{2}:\d{2}:\d{2})\b/', $ln, $t);
                $procStartTime = $timeMatch
                    ? Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$t[1]}")
                    : null;

                // inside procedure
                if ($currentProcedure && $currentProject) {
                    $processMap[$counter]['proc_name'] = $procName;
                    $processMap[$counter]['total_seconds'] = 0;
                    $processMap[$counter]['project_id'] = $currentProject->id ?? 0;
                    $processMap[$counter]['bauteil_id'] = $parentId ?? null;
                    $processMap[$counter]['start_time'] = $procStartTime;
                    $processMap[$counter]['source_file'] = $sourceFile ?? null;
                }
                // standalone (outside procedure)
                else {
                    $standaloneName = $procName;
                    $standaloneStart = $procStartTime;
                }
                continue;
            }

            // --- STEP 4: Execution time ---
            if (preg_match('/\s(\d{2}:\d{2}:\d{2})\s+IEX_\d+\s+FILE EXECUTION TIME/', $ln, $m)) {
                $endTime = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}");

                if ($currentProcName && $currentProcedure) {
                    // add to process inside procedure
                    $seconds = $procStartTime->diffInSeconds($endTime);
                    $processMap[$counter]['total_seconds'] = $seconds;
                    $processMap[$counter]['end_time'] = $endTime;
                    $counter++;
                } elseif ($standaloneName && $standaloneStart) {

                    // Try to detect project from path name if possible (optional future logic)
                    $seconds = $standaloneStart->diffInSeconds($endTime);
                    $project = $currentProject; // may be null if no active procedure
                    $standaloneName = preg_replace('/[^\x20-\x7E_\-\.]/', '', $standaloneName);

                    Process::firstOrCreate([
                        'project_id' => $project?->id,
                        'procedure_id' => null,
                        'bauteil_id' => (($parentId) ? $parentId : null),
                        'name' => $standaloneName,
                        'start_time' => $standaloneStart,
                        'end_time' => $endTime,
                        'total_seconds' => $seconds,
                        'source_file' => $sourceFile,
                    ]);

                    $this->info("💾 Stored process without any procedure: {$standaloneName} ({$seconds}s)");
                    $standaloneName = null;
                    $standaloneStart = null;
                }
                continue;
            }

            // --- STEP 5: END OF PROCEDURE ---
            if (preg_match('/ENDE DER PROZEDUR/i', $ln)) {
                if ($currentProcedure && $currentProject) {
                    if (preg_match('/\b(\d{2}:\d{2}:\d{2})\b/', $ln, $t)) {
                        $endTime = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$t[1]}")->toDateTimeString();
                        $currentProcedure['end_time'] = $endTime;
                    }
                    $this->storeProcedure($currentProject, $currentProcedure, $processMap);
                    $currentProcedure = null;
                    $processMap = [];
                }
                continue;
            }
        }

        if ($currentProcedure && $currentProject) {
            $this->storeProcedure($currentProject, $currentProcedure, $processMap);
        }

        rename($path, $processedDir . $filename);

        $this->info('✅ Parsing complete.');
        return 0;
    }

    protected function storeProcedure(Project $project, array $procData, array $processMap)
    {
        $exists = Procedure::where('project_id', $project->id)
            ->where('start_time', $procData['start_time'])
            ->first();

        if ($exists) {
            $this->info("⚠️ Skipping duplicate procedure for project {$project->project_name}");
            return;
        }

        $proc = $project->procedures()->create([
            'start_time' => $procData['start_time'],
            'end_time' => $procData['end_time'] ?? null,
            'process_count' => count($processMap),
            'source_file' => $procData['source_file'] ?? null,
        ]);

        foreach ($processMap as $name => $data) {
            $proc->processes()->firstOrCreate([
                'name' => $data['proc_name'],
                'project_id' => $data['project_id'],
                'bauteil_id' => $data['bauteil_id'] ?? null,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'source_file' => $data['source_file'],
                'total_seconds' => $data['total_seconds'] ?? 0,
            ]);
        }

        $this->info("💾 Stored procedure for project {$project->project_name} with " . count($processMap) . " processes.");
    }
}
