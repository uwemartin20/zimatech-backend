<?php

namespace App\Domains\PrinterProblems\Enums;

enum IssueType: string
{
    case Mechanical  = 'mechanical';
    case Electrical  = 'electrical';
    case Software    = 'software';
    case Material    = 'material';
    case Calibration = 'calibration';
    case Unknown     = 'unknown';

    public function label(): string
    {
        return match($this) {
            self::Mechanical  => 'Mechanisch',
            self::Electrical  => 'Elektrisch',
            self::Software    => 'Software',
            self::Material    => 'Material',
            self::Calibration => 'Kalibrierung',
            self::Unknown     => 'Unbekannt',
        };
    }
}