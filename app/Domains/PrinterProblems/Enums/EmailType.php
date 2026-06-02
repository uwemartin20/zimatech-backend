<?php

namespace App\Domains\PrinterProblems\Enums;

enum EmailType: string
{
    case AiDraft           = 'ai_draft';
    case UserEdited        = 'user_edited';
    case ManufacturerReply = 'manufacturer_reply';

    public function label(): string
    {
        return match($this) {
            self::AiDraft           => 'KI-Entwurf',
            self::UserEdited        => 'Bearbeitet',
            self::ManufacturerReply => 'Herstellerantwort',
        };
    }
}