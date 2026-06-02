<?php

namespace App\Domains\PrinterProblems\Enums;

enum EmailDirection: string
{
    case Outgoing = 'outgoing';
    case Incoming = 'incoming';
}