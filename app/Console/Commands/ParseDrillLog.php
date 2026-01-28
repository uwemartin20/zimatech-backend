<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Procedure;
use App\Models\Process;
use App\Models\ProcessPause;
use App\Models\Bauteil;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ParseDrillLog extends Command
{
    protected $signature = 'parse:drilllog {path} {--save=true : Persist parsed data to the database}';
    protected $description = 'Parse drilling machine log and store data in database';

    public function handle()
    {
        $path = $this->argument('path');
        if (!file_exists($path)) {
            $this->error("File not found: $path");
            return 1;
        }

        $saveToDb = filter_var($this->option('save'), FILTER_VALIDATE_BOOLEAN);
        $this->info('Save to DB: ' . ($saveToDb ? 'YES' : 'NO (dry-run)'));

        $processedDir = dirname($path) . '/processed/';
        $file_name = basename($path);
        $firstDate = null;

        $lines = file($path, FILE_IGNORE_NEW_LINES);
        $this->info("Loaded " . count($lines) . " lines.");

        $logDate = null;
        $currentProject = null;
        $currentProcedure = null;
        $currentProcName = null;
        $processMap = [];
        $counter = 0;

        // For direct runs (outside procedure)
        $standaloneStart = null;
        $standaloneName = null;

        $currentPause = null;     // active pause
        $pauseBuffer = [];       // pauses for current process
        $machineId = 2;

        foreach ($lines as $i => $line) {
            $ln = trim($line);

            // --- STEP 1: Detect log date ---
            if (preg_match('/<<\s*(\d{1,2}-[A-Za-z]{3}-\d{4})\s*>>/', $ln, $m)) {
                if (!$firstDate) { // only the first occurrence
                    $firstDate = Carbon::createFromFormat('d-M-Y', $m[1]);

                    $year = $firstDate->format('Y');
                    $week = $firstDate->format('W'); // ISO-8601 week number

                    // Build processed directory
                    $processedDir = dirname($path) . "/processed/{$year}/{$week}/";
                    if (!is_dir($processedDir)) {
                        mkdir($processedDir, 0755, true);
                    }
                }
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
                if ($saveToDb) {
                    $currentProject = Project::firstOrCreate(
                        ['auftragsnummer_zt' => $auftragsnummer, 'project_name' => $projectName, 'from_machine_logs' => 1]
                    );
                } else {
                    $currentProject = new Project([
                        'id' => null,
                        'project_name' => $projectName,
                        'auftragsnummer_zt' => $auftragsnummer
                    ]);
                }
                // $currentProject = Project::firstOrCreate(
                //     ['auftragsnummer_zt' => $auftragsnummer, 'project_name' => $projectName, 'from_machine_logs' => 1]
                // );

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
                $currentPause = null;     // active pause
                $pauseBuffer = [];
                
                
                // Extract full path after "FROM IPC:"
                if (preg_match('/FROM IPC:([^\s]+)/i', $line, $matches)) {
                    $fullPath = $matches[1]; // full path
                    $sourceFile = mb_convert_encoding($fullPath, 'UTF-8', 'auto');

                    // Split by backslash
                    $parts = preg_split('/\\\\/', $fullPath);
                    $filename = strtoupper(basename($fullPath));

                    $projectName = null;
                    $auftragsnummer = null;
                    $bauteile = [];
                    if (($filename === "NULLEN.FID") || (stripos($parts[count($parts)-1], '.fid') === false)) continue;
                    
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
                    if ($saveToDb) {
                        $currentProject = Project::firstOrCreate(
                            ['auftragsnummer_zt' => $auftragsnummer, 'project_name' => $projectName, 'from_machine_logs' => 1]
                        );
                    } else {
                        $currentProject = new Project([
                            'id' => null,
                            'project_name' => $projectName,
                            'auftragsnummer_zt' => $auftragsnummer
                        ]);
                    }

                    $parentId = null; // No parent initially

                    foreach ($bauteile as $bauteilName) {
                        if ($saveToDb) {
                            $bauteil = Bauteil::firstOrCreate(
                                [
                                    'name' => mb_convert_encoding($bauteilName, 'UTF-8', 'auto'),
                                    'project_id' => $currentProject->id,
                                    'parent_id' => $parentId
                                ]
                            );

                            // Next bauteil’s parent is the current one
                            $parentId = $bauteil->id;
                        } else {
                            $this->line("  └─ Bauteil: {$bauteilName}");
                        }
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
                    $processMap[$counter] = [
                        'proc_name' => $procName,
                        'total_seconds' => 0,
                        'project_id' => $currentProject->id ?? 0,
                        'bauteil_id' => $parentId ?? null,
                        'machine_id' => $machineId,
                        'start_time' => $procStartTime,
                        'source_file' => $sourceFile ?? null,
                        'pauses' => [],   // <-- ADD THIS
                    ];
                }
                // standalone (outside procedure)
                else {
                    $standaloneName = $procName;
                    $standaloneStart = $procStartTime;
                }
                continue;
            }

            // --- PAUSE START ---
            if ($currentProcName && !$currentPause) {

                // M00 Program Stop
                if (preg_match('/\b(\d{2}:\d{2}:\d{2})\s+IEX_\d+\s+M00:\s+PROGRAM STOP\b/i', $ln, $m)) {
                    $currentPause = [
                        'start' => Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}"),
                        'type' => 'M00_PROGRAM_STOP',
                        'reason' => 'M00 Program Stop',
                    ];
                    continue;
                }

                // HOLD key
                if (preg_match('/\b(\d{2}:\d{2}:\d{2})\s+DPS_\d+\s+PRESSED THE HOLD KEY\b/i', $ln, $m)) {
                    $currentPause = [
                        'start' => Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}"),
                        'type' => 'HOLD_KEY',
                        'reason' => 'Pressed HOLD key',
                    ];
                    continue;
                }
            }

            // --- PAUSE END ---
            if ($currentPause) {

                // Resume from M00
                if (
                    $currentPause['type'] === 'M00_PROGRAM_STOP' &&
                    preg_match('/\b(\d{2}:\d{2}:\d{2})\s+DPS_\d+\s+PRESSED THE START KEY\b/i', $ln, $m)
                ) {
                    $currentPause['end'] = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}");
                    $processMap[$counter]['pauses'][] = $currentPause;
                    $pauseBuffer[] = $currentPause;
                    $currentPause = null;
                    continue;
                }

                // Resume from HOLD
                if (
                    $currentPause['type'] === 'HOLD_KEY' &&
                    preg_match('/\b(\d{2}:\d{2}:\d{2})\s+DPS_\d+\s+RELEASED THE RELEASE KEY\b/i', $ln, $m)
                ) {
                    $currentPause['end'] = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}");
                    $processMap[$counter]['pauses'][] = $currentPause;
                    $pauseBuffer[] = $currentPause;
                    $currentPause = null;
                    continue;
                }
            }

            // --- STEP 4: Execution time ---
            if (
                preg_match('/\s(\d{2}:\d{2}:\d{2})\s+IEX_\d+\s+FILE EXECUTION TIME/', $ln, $m)
                ||
                // CNC turned OFF because of an error
                preg_match('/\b(\d{1,2}:\d{2}:\d{2})\s+ICN_\d+\s+CNC OFF\b/i', $ln, $m)
            ) {
                $endTime = Carbon::createFromFormat('d-M-Y H:i:s', "$logDate {$m[1]}");

                if ($currentPause) {
                    $currentPause['end'] = $endTime;
                    $pauseBuffer[] = $currentPause;
                    $currentPause = null;
                }

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

                    if ($saveToDb) {
                        $process = Process::firstOrCreate([
                            'project_id' => $project?->id,
                            'procedure_id' => null,
                            'bauteil_id' => (($parentId) ? $parentId : null),
                            'name' => $standaloneName,
                            'start_time' => $standaloneStart,
                            'end_time' => $endTime,
                            'total_seconds' => $seconds,
                            'source_file' => $sourceFile,
                        ]);

                        // store pauses
                        foreach ($pauseBuffer as $pause) {
                            ProcessPause::firstOrCreate([
                                'process_id' => $process->id,
                                'pause_start' => $pause['start'],
                                'pause_end' => $pause['end'] ?? null,
                                'pause_type' => $pause['type'],
                                'reason' => $pause['reason'],
                            ]);
                        }

                        // reset
                        $pauseBuffer = [];
                        $currentPause = null;
                    } else {
                        $this->line("🧪 Standalone process:");
                        $this->line(json_encode([
                            'name' => $standaloneName,
                            'start' => $standaloneStart,
                            'end' => $endTime,
                            'seconds' => $seconds,
                            'source' => $sourceFile
                        ], JSON_PRETTY_PRINT));
                    }

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
                    $this->storeProcedure($currentProject, $currentProcedure, $processMap, $saveToDb);
                    $currentProcedure = null;
                    $processMap = [];
                }
                continue;
            }
        }

        if ($currentProcedure && $currentProject) {
            $this->storeProcedure($currentProject, $currentProcedure, $processMap, $saveToDb);
        }

        if ($saveToDb)
            rename($path, $processedDir . $file_name);

        $this->info('✅ Parsing complete.');
        return 0;
    }

    protected function storeProcedure(Project $project, array $procData, array $processMap, bool $saveToDb = true)
    {
        if (!$saveToDb) {
            $this->line("🧪 Procedure (dry-run):");
            $this->line(json_encode([
                'project' => $project->project_name ?? null,
                'start' => $procData['start_time'],
                'end' => $procData['end_time'],
                'processes' => $processMap
            ], JSON_PRETTY_PRINT));
            return;
        }
        $exists = Procedure::where('project_id', $project->id)
            ->where('start_time', $procData['start_time'])
            ->first();

        if ($exists) {
            $this->info("⚠️ Skipping duplicate procedure for project {$project->project_name}");
            return;
        }

        $proc = $project->procedures()->firstOrCreate([
            'start_time' => $procData['start_time'],
            'end_time' => $procData['end_time'] ?? null,
            'process_count' => count($processMap),
            'source_file' => $procData['source_file'] ?? null,
        ]);

        foreach ($processMap as $name => $data) {
            $process = $proc->processes()->firstOrCreate([
                'name' => $data['proc_name'],
                'project_id' => $data['project_id'],
                'bauteil_id' => $data['bauteil_id'] ?? null,
                'machine_id' => $data['machine_id'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'source_file' => $data['source_file'],
                'total_seconds' => $data['total_seconds'] ?? 0,
            ]);

            foreach ($data['pauses'] ?? [] as $pause) {
                ProcessPause::firstOrcreate([
                    'process_id' => $process->id,
                    'pause_start' => $pause['start'],
                    'pause_end' => $pause['end'] ?? null,
                    'pause_type' => $pause['type'],
                    'reason' => $pause['reason'],
                ]);
            }
        }

        $this->info("💾 Stored procedure for project {$project->project_name} with " . count($processMap) . " processes.");
    }
}