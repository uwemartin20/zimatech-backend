<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\IOFactory;
use PhpOffice\PhpPresentation\Style\Alignment;

class AdminProjectController extends Controller
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

        return view("admin.projects.index", compact("projects"));

    }

    public function create()
    {
        return view('admin.projects.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'kunde' => 'required|string|max:255',
            'auftragsnummer' => 'required|string|max:255|unique:projects,auftragsnummer',
            'project_name' => 'required|string|max:255',
        ]);

        // Save project to DB
        if ($request->has('save_to_db')) {
        // $project = Project::create([
        //     'auftragsnummer' => $request->auftragsnummer,
        //     'project_name' => $request->project_name,
        // ]);
        }

        // Create folder structure
        $this->createProject($request->kunde, $request->auftragsnummer, $request->project_name);

        return redirect()->route('admin.projects')->with('success', 'Project created successfully!');
    }

    public function createProject($kunde = 'KundeName', $auftragsnummer = '12345', $projekt = 'ProjektName')
    {
        // Base path inside storage/app
        $basePath = storage_path("app/{$kunde}/{$auftragsnummer}_{$projekt}");

        // Define folder structure
        $folders = [
            $basePath . '/001_Historie',
            $basePath . '/01_Eingangsdaten/Schriftliche_Freigabe',
            $basePath . '/02_Arbeitsverzeichnis_In Arbeit/Teile_Bezeichnung',
            $basePath . '/03_Ausgangsdaten',
            $basePath . '/04_Fraesdaten',
            $basePath . '/05_CAD-Daten zum Messen',
            $basePath . '/06_Messberichte',
            $basePath . '/07_Dokumentation',
            $basePath . '/08_Temp/Bauteil 1/Bearbeitungsplan',
            $basePath . '/08_Temp/Bauteil 1/Iges',
            $basePath . '/08_Temp/Bauteil 1/NC-Prg',

        ];

        for ($i = 1; $i <= 10; $i++) {
            $bauteil_dir = $basePath . '/04_Fraesdaten/Bauteil ' . $i;
            array_push($folders, $bauteil_dir.'/Bearbeitungsplan');
            array_push($folders, $bauteil_dir.'/Iges');
            array_push($folders, $bauteil_dir.'/NC-Prg');
        }

        // Create folders
        foreach ($folders as $folder) {
            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }
        }

        $createFiles = [
            [
                'name' => $basePath . '/001_Historie//'.$auftragsnummer.'_'.$projekt.'_historie', 
                'type' => 'xlsx'
            ],
            [
                'name' => $basePath . '/07_Dokumentation//'.$auftragsnummer.'_'.$projekt.'_Bezeichnung_Projektplan', 
                'type' => 'xlsx'
            ],
            [
                'name' => $basePath . '/07_Dokumentation/Einzelteilübersicht_Vorlage_'. Carbon::now()->format('d-m-Y'), 
                'type' => 'xlsx'
            ],
            [
                'name' => $basePath . '/07_Dokumentation/Dokumentation_TZ_1', 
                'type' => 'ppt'
            ],
            [
                'name' => $basePath . '/02_Arbeitsverzeichnis_In Arbeit/0_StandartNotiz_Name', 
                'type' => 'txt'
            ],
        ];

        // Create folders
        foreach ($createFiles as $file) {
            $filename = $file['name'];
            $type = $file['type'];
            if (!file_exists($filename)) {
                $this->createNewFile($filename, $type);
            }
        }

        // // Create initial history Excel file
        // $historyFile = $basePath . '/001_History//'.$auftragsnummer.'_'.$projekt.'_history.xlsx';
        // if (!file_exists($historyFile)) {
        //     $spreadsheet = new Spreadsheet();
        //     $sheet = $spreadsheet->getActiveSheet();
        //     $sheet->setCellValue('A1', 'Projekt History');
        //     $writer = new Xlsx($spreadsheet);
        //     $writer->save($historyFile);
        // }

        // // Create initial arbeitsverzeichnis Txt file
        // $verzeichnisFile = $basePath . '/02_Arbeitsverzeichnis_In Arbeit/0_StandartNotiz_Name.txt';
        // if (!file_exists($verzeichnisFile)) {
        //     file_put_contents($verzeichnisFile, "");
        // }

        return $basePath;
    }

    public function createNewFile($filename, $type) {
        $fullname = $filename .'.'. $type;
        switch ($type) {
            case "txt":
                file_put_contents($fullname, "");
                break;
            case "xlsx":
                $spreadsheet = new Spreadsheet();
                $sheet = $spreadsheet->getActiveSheet();
                $writer = new Xlsx($spreadsheet);
                $writer->save($fullname);
                break;
            case "ppt":
                $ppt = new PhpPresentation();
                $slide = $ppt->getActiveSlide();
                $shape = $slide->createRichTextShape()
                    ->setHeight(50)
                    ->setWidth(600)
                    ->setOffsetX(170)
                    ->setOffsetY(180);
                $shape->getActiveParagraph()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $textRun = $shape->createTextRun('New Presentation');
                $textRun->getFont()->setBold(true)->setSize(24);

                $writer = IOFactory::createWriter($ppt, 'PowerPoint2007');
                $writer->save($fullname);
                break;
            default:
                throw new \Exception("Unsupported file type: $type");
        }
    }

    public function projectLogs(Request $request)
    {
        $query = Project::with(['procedures.processes', 'processes', 'bauteile.processes'])
            ->where(function ($q) {
                $q->whereHas('processes')
                ->orWhereHas('procedures.processes')
                ->orWhereHas('bauteile.processes');
            });

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
        $allProjects = Project::orderBy('project_name')
            ->where(function ($q) {
                $q->whereHas('processes')
                ->orWhereHas('procedures.processes')
                ->orWhereHas('bauteile.processes');
            })
            ->get();

        return view('admin.projects.logs', compact('projects', 'allProjects'));
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
