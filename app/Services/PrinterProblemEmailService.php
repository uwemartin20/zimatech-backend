<?php

namespace App\Services;

use App\Models\PrinterProblem;
use App\Models\PrinterProblemEmail;
use Illuminate\Support\Facades\Auth;

class PrinterProblemEmailService
{
    public function __construct(
        private readonly AiAssistantService $aiService,
    ) {}

    /**
     * Generate the first AI email draft and save it.
     * Called when no emails exist yet for a problem.
     */
    public function generateDraft(PrinterProblem $problem): array
    {
        $ai     = $this->aiService->generateEmailDraft($problem);
        $result = $ai['result'] ?? [];

        return [
            'subject' => $result['subject'] ?? null,
            'body'    => $result['body']    ?? $ai['raw_text'] ?? '',
        ];
    }

    /**
     * Rewrite the latest draft based on user remarks.
     * Passes full email history as context.
     */
    public function rewriteDraft(PrinterProblem $problem, string $remarks): array
    {
        $emails = $problem->emails()->orderBy('created_at')->get();

        $ai     = $this->aiService->rewriteEmailDraft($problem, $emails, $remarks);
        $result = $ai['result'] ?? [];

        return [
            'subject' => $result['subject'] ?? null,
            'body'    => $result['body']    ?? $ai['raw_text'] ?? '',
        ];
    }

    /**
     * Save the user's approved/edited version of a draft.
     */
    public function saveUserDraft(
        PrinterProblem $problem,
        string $subject,
        string $body
    ): PrinterProblemEmail {
        return PrinterProblemEmail::create([
            'problem_id'   => $problem->id,
            'direction'    => 'outgoing',
            'email_type'   => 'user_edited',
            'subject'      => $subject,
            'body'         => $body,
            'ai_generated' => false,
            'created_by'   => Auth::id(),
        ]);
    }

    /**
     * Store an incoming manufacturer reply.
     */
    public function storeManufacturerReply(
        PrinterProblem $problem,
        string $body,
        ?string $subject = null
    ): PrinterProblemEmail {
        return PrinterProblemEmail::create([
            'problem_id'   => $problem->id,
            'direction'    => 'incoming',
            'email_type'   => 'manufacturer_reply',
            'subject'      => $subject,
            'body'         => $body,
            'ai_generated' => false,
            'created_by'   => Auth::id(),
        ]);
    }

    /**
     * Load all emails for a problem ordered chronologically.
     */
    public function getEmailThread(PrinterProblem $problem)
    {
        return $problem->emails()->orderBy('created_at')->get();
    }
}