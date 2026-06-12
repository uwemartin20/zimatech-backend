<?php

namespace App\Repositories;

use App\Domains\PrinterProblems\DTOs\CreateProblemDTO;
use App\Domains\PrinterProblems\DTOs\UpdateProblemDTO;
use App\Interfaces\PrinterProblemRepositoryInterface;
use App\Models\PrinterProblem;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentPrinterProblemRepository implements PrinterProblemRepositoryInterface
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PrinterProblem::with('creator')->latest();

        if (! empty($filters['problem_uid'])) {
            $query->where('problem_uid', 'like', '%'.$filters['problem_uid'].'%');
        }

        if (! empty($filters['machine_error_id'])) {
            $query->where('machine_error_id', 'like', '%'.$filters['machine_error_id'].'%');
        }

        if (! empty($filters['material'])) {
            $query->where('material', 'like', '%'.$filters['material'].'%');
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Generic keyword search across description fields
        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term) {
                $q->where('short_description', 'like', "%{$term}%")
                    ->orWhere('operator_explanation', 'like', "%{$term}%")
                    ->orWhere('problem_uid', 'like', "%{$term}%")
                    ->orWhere('machine_error_id', 'like', "%{$term}%")
                    ->orWhere('material', 'like', "%{$term}%");
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function findById(int $id): ?PrinterProblem
    {
        return PrinterProblem::with(['attachments', 'creator'])->find($id);
    }

    public function findByUid(string $uid): ?PrinterProblem
    {
        return PrinterProblem::with(['attachments', 'emails.creator', 'creator'])
            ->where('problem_uid', $uid)
            ->first();
    }

    public function create(CreateProblemDTO $dto): PrinterProblem
    {
        return PrinterProblem::create([
            'problem_uid' => $this->generateUid(),
            'order_number' => $dto->order_number,
            'designation' => $dto->designation,
            'version_number' => $dto->version_number,
            'design_nozzle_diameter' => $dto->design_nozzle_diameter,
            'tool_nozzle_diameter' => $dto->tool_nozzle_diameter,
            'material' => $dto->material,
            'print_temperature' => $dto->print_temperature,
            'bed_temperature' => $dto->bed_temperature,
            'nozzle_height' => $dto->nozzle_height,
            'offset_x' => $dto->offset_x,
            'offset_y' => $dto->offset_y,
            'offset_z' => $dto->offset_z,
            'maintenance_completed' => $dto->maintenance_completed,
            'machine_error_id' => $dto->machine_error_id,
            'short_description' => $dto->short_description,
            'operator_explanation' => $dto->operator_explanation,
            'status' => 'open',
            'created_by' => $dto->created_by,
        ]);
    }

    public function update(PrinterProblem $problem, UpdateProblemDTO $dto): PrinterProblem
    {
        $problem->update([
            'order_number' => $dto->order_number,
            'designation' => $dto->designation,
            'version_number' => $dto->version_number,
            'design_nozzle_diameter' => $dto->design_nozzle_diameter,
            'tool_nozzle_diameter' => $dto->tool_nozzle_diameter,
            'material' => $dto->material,
            'print_temperature' => $dto->print_temperature,
            'bed_temperature' => $dto->bed_temperature,
            'nozzle_height' => $dto->nozzle_height,
            'offset_x' => $dto->offset_x,
            'offset_y' => $dto->offset_y,
            'offset_z' => $dto->offset_z,
            'maintenance_completed' => $dto->maintenance_completed,
            'machine_error_id' => $dto->machine_error_id,
            'short_description' => $dto->short_description,
            'operator_explanation' => $dto->operator_explanation,
            'status' => $dto->status,
        ]);

        return $problem->fresh();
    }

    public function delete(PrinterProblem $problem): void
    {
        $problem->delete();
    }

    public function generateUid(): string
    {
        $year = now()->year;
        $prefix = "PRB-{$year}-";
        $lastUid = PrinterProblem::where('problem_uid', 'like', $prefix.'%')
            ->orderByDesc('problem_uid')
            ->value('problem_uid');

        $next = $lastUid
            ? (int) substr($lastUid, -5) + 1
            : 1;

        return $prefix.str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
