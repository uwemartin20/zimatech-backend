<?php

namespace App\Domains\PrinterProblems\DTOs;

class CreateProblemDTO
{
    public function __construct(
        // Projekt Info
        public readonly ?string $order_number,
        public readonly ?string $designation,
        public readonly ?string $version_number,

        // Machine Settings
        public readonly ?string $design_nozzle_diameter,
        public readonly ?string $tool_nozzle_diameter,
        public readonly ?string $material,
        public readonly ?float  $print_temperature,
        public readonly ?float  $bed_temperature,
        public readonly ?float  $nozzle_height,

        // Path Offsets
        public readonly ?float $offset_x,
        public readonly ?float $offset_y,
        public readonly ?float $offset_z,

        // Maintenance
        public readonly bool $maintenance_completed,

        // Error Details
        public readonly ?string $machine_error_id,
        public readonly ?string $short_description,
        public readonly ?string $operator_explanation,

        public readonly int $created_by,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            order_number:         $data['order_number']         ?? null,
            designation:          $data['designation']          ?? null,
            version_number:       $data['version_number']       ?? null,
            design_nozzle_diameter:    $data['design_nozzle_diameter']        ?? null,
            tool_nozzle_diameter: $data['tool_nozzle_diameter'] ?? null,
            material:             $data['material']             ?? null,
            print_temperature:    isset($data['print_temperature'])  ? (float) $data['print_temperature']  : null,
            bed_temperature:      isset($data['bed_temperature'])    ? (float) $data['bed_temperature']    : null,
            nozzle_height:        isset($data['nozzle_height'])      ? (float) $data['nozzle_height']      : null,
            offset_x:             isset($data['offset_x'])           ? (float) $data['offset_x']           : null,
            offset_y:             isset($data['offset_y'])           ? (float) $data['offset_y']           : null,
            offset_z:             isset($data['offset_z'])           ? (float) $data['offset_z']           : null,
            maintenance_completed:     (bool) ($data['maintenance_completed']  ?? false),
            machine_error_id:     $data['machine_error_id']             ?? null,
            short_description:    $data['short_description']    ?? null,
            operator_explanation: $data['operator_explanation'] ?? null,
            created_by:           $userId,
        );
    }
}