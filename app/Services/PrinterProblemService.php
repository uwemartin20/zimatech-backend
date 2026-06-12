<?php

namespace App\Services;

use App\Domains\PrinterProblems\DTOs\CreateProblemDTO;
use App\Domains\PrinterProblems\DTOs\UpdateProblemDTO;
use App\Interfaces\PrinterProblemRepositoryInterface;
use App\Models\PrinterProblem;
use Illuminate\Pagination\LengthAwarePaginator;

class PrinterProblemService
{
    private AiAssistantService $aiAssistantService;

    public function __construct(
        private readonly PrinterProblemRepositoryInterface $repository,
        AiAssistantService $aiAssistantService,
    ) {
        $this->aiAssistantService = $aiAssistantService;
    }

    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function findOrFail(int $id): PrinterProblem
    {
        $problem = $this->repository->findById($id);

        if (! $problem) {
            abort(404, 'Problem nicht gefunden.');
        }

        return $problem;
    }

    public function create(CreateProblemDTO $dto): PrinterProblem
    {
        return $this->repository->create($dto);
    }

    public function update(PrinterProblem $problem, UpdateProblemDTO $dto): PrinterProblem
    {
        return $this->repository->update($problem, $dto);
    }

    public function delete(PrinterProblem $problem): void
    {
        $this->repository->delete($problem);
    }

    public function generateAiRecommendations(
        PrinterProblem $problem
    ): PrinterProblem {
        $analysis = $this->aiAssistantService
            ->generateProblemRecommendations($problem);

        $result = $analysis['result'] ?? [];

        $problem->update([
            'issue_type' => $result['issue_type'] ?? null,

            'ai_troubleshooting' => ! empty($result['troubleshooting_steps'])
                ? implode("\n", $result['troubleshooting_steps'])
                : null,

            'ai_next_steps' => ! empty($result['next_steps'])
                ? implode("\n", $result['next_steps'])
                : null,
        ]);

        return $problem->fresh();
    }
}
