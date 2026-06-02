<?php

namespace App\Domains\PrinterProblems\Enums;

enum AttachmentType: string
{
    case Image = 'image';
    case Pdf   = 'pdf';
}