<?php

namespace App\Interfaces;

use App\Domains\PrinterProblems\DTOs\CreateProblemDTO;
use App\Domains\PrinterProblems\DTOs\UpdateProblemDTO;
use App\Models\PrinterProblem;
use Illuminate\Pagination\LengthAwarePaginator;

interface PrinterProblemRepositoryInterface
{
    /**
     * Paginated list with optional search filters.
     *
     * @param  array{
     *   search?: string,
     *   problem_uid?: string,
     *   error_id?: string,
     *   material?: string,
     *   status?: string,
     * } $filters
     */
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function findById(int $id): ?PrinterProblem;

    public function findByUid(string $uid): ?PrinterProblem;

    public function create(CreateProblemDTO $dto): PrinterProblem;

    public function update(PrinterProblem $problem, UpdateProblemDTO $dto): PrinterProblem;

    public function delete(PrinterProblem $problem): void;

    public function generateUid(): string;
}
