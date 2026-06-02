<?php

namespace App\Http\Controllers;

use App\Services\PrinterProblemService;
use App\Domains\PrinterProblems\DTOs\CreateProblemDTO;
use App\Domains\PrinterProblems\DTOs\UpdateProblemDTO;
use App\Domains\PrinterProblems\Enums\ProblemStatus;
use App\Http\Requests\StorePrinterProblemRequest;
use App\Http\Requests\UpdatePrinterProblemRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrinterProblemController extends Controller
{
    public function __construct(
        private readonly PrinterProblemService $service,
    ) {}

    // -------------------------------------------------------------------------
    // GET /printer-problems
    // -------------------------------------------------------------------------
    public function index(Request $request)
    {
        $filters  = $request->only(['search', 'problem_uid', 'machine_error_id', 'material', 'status']);
        $problems = $this->service->list($filters);
        $statuses = ProblemStatus::cases();

        return view('user.printer-problems.index', compact('problems', 'filters', 'statuses'));
    }

    // -------------------------------------------------------------------------
    // GET /printer-problems/create
    // -------------------------------------------------------------------------
    public function create()
    {
        return view('user.printer-problems.create');
    }

    // -------------------------------------------------------------------------
    // POST /printer-problems
    // -------------------------------------------------------------------------
    public function store(StorePrinterProblemRequest $request)
    {
        $dto     = CreateProblemDTO::fromArray($request->validated(), Auth::id());
        $problem = $this->service->create($dto);

        return redirect()
            ->route('printer-problems.show', $problem->id)
            ->with('success', "Problem {$problem->problem_uid} wurde erfolgreich angelegt.");
    }

    // -------------------------------------------------------------------------
    // GET /printer-problems/{id}
    // -------------------------------------------------------------------------
    public function show(int $id)
    {
        $problem = $this->service->findOrFail($id);

        $aiSuggestions = null;

        if (
            !$problem->issue_type ||
            !$problem->ai_troubleshooting
        ) {
            $aiSuggestions = $this->service
                ->generateAiRecommendations($problem);
        }

        return view('user.printer-problems.show', compact('problem', 'aiSuggestions'));
    }

    // -------------------------------------------------------------------------
    // GET /printer-problems/{id}/edit
    // -------------------------------------------------------------------------
    public function edit(int $id)
    {
        $problem  = $this->service->findOrFail($id);
        $statuses = ProblemStatus::cases();

        return view('user.printer-problems.edit', compact('problem', 'statuses'));
    }

    // -------------------------------------------------------------------------
    // PUT /printer-problems/{id}
    // -------------------------------------------------------------------------
    public function update(UpdatePrinterProblemRequest $request, int $id)
    {
        $problem = $this->service->findOrFail($id);
        $dto     = UpdateProblemDTO::fromArray($request->validated());
        $this->service->update($problem, $dto);

        return redirect()
            ->route('printer-problems.show', $problem->id)
            ->with('success', "Problem {$problem->problem_uid} wurde aktualisiert.");
    }

    // -------------------------------------------------------------------------
    // DELETE /printer-problems/{id}
    // -------------------------------------------------------------------------
    public function destroy(int $id)
    {
        $problem = $this->service->findOrFail($id);
        $uid     = $problem->problem_uid;
        $this->service->delete($problem);

        return redirect()
            ->route('printer-problems.index')
            ->with('success', "Problem {$uid} wurde gelöscht.");
    }
}