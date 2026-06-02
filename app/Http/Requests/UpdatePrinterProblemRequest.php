<?php

namespace App\Http\Requests;

use App\Domains\PrinterProblems\Enums\ProblemStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePrinterProblemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_number'         => ['nullable', 'string', 'max:100'],
            'designation'          => ['nullable', 'string', 'max:100'],
            'version_number'       => ['nullable', 'string', 'max:50'],
            'design_nozzle_diameter'        => ['nullable', 'string', 'max:100'],
            'tool_nozzle_diameter'          => ['nullable', 'string', 'max:100'],
            'material'             => ['nullable', 'string', 'max:100'],
            'print_temperature'    => ['nullable', 'numeric', 'min:0', 'max:500'],
            'bed_temperature'      => ['nullable', 'numeric', 'min:0', 'max:300'],
            'nozzle_height'        => ['nullable', 'numeric'],
            'offset_x'        => ['nullable', 'numeric'],
            'offset_y'        => ['nullable', 'numeric'],
            'offset_z'        => ['nullable', 'numeric'],
            'maintenance_completed'     => ['nullable', 'boolean'],
            'machine_error_id'             => ['nullable', 'string', 'max:100'],
            'short_description'    => ['required', 'string', 'max:255'],
            'operator_explanation' => ['nullable', 'string', 'max:5000'],
            'status'               => ['required', Rule::enum(ProblemStatus::class)],
        ];
    }

    public function attributes(): array
    {
        return [
            'order_number'         => 'Auftragsnummer',
            'designation'          => 'Kennzeichnung',
            'version_number'       => 'Versionsnummer',
            'design_nozzle_diameter'        => 'Düsendesign',
            'tool_nozzle_diameter'          => 'Düsenwerkzeug',
            'material'             => 'Material',
            'print_temperature'    => 'Drucktemperatur',
            'bed_temperature'      => 'Tischtemperatur',
            'nozzle_height'        => 'Düsenhöhe',
            'offset_x'        => 'Offset X',
            'offset_y'        => 'Offset Y',
            'offset_z'        => 'Offset Z',
            'maintenance_completed'     => 'Wartung gemacht',
            'machine_error_id'             => 'Fehler-ID',
            'short_description'    => 'Kurzbeschreibung',
            'operator_explanation' => 'Bedienererklärung',
            'status'               => 'Status',
        ];
    }
}