<?php

namespace App\Domains\PrinterProblems\Enums;

enum ProblemStatus: string
{
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Open => 'Offen',
            self::Closed => 'Geschlossen',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Open => 'badge bg-warning text-dark',
            self::Closed => 'badge bg-success',
        };
    }
}
