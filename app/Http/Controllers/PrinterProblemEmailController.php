<?php

namespace App\Http\Controllers;

use App\Http\Requests\RewriteEmailRequest;
use App\Http\Requests\SaveEmailDraftRequest;
use App\Http\Requests\StoreManufacturerReplyRequest;
use App\Services\PrinterProblemEmailService;
use App\Services\PrinterProblemService;
use Illuminate\Http\JsonResponse;

class PrinterProblemEmailController extends Controller
{
    public function __construct(
        private readonly PrinterProblemService      $problemService,
        private readonly PrinterProblemEmailService $emailService,
    ) {}

    // -------------------------------------------------------------------------
    // GET /printer-problems/{problem}/emails
    // Returns the full email thread as JSON — consumed by the modal via fetch()
    // -------------------------------------------------------------------------
    public function index(int $problem): JsonResponse
    {
        $printerProblem = $this->problemService->findOrFail($problem);
        $emails         = $this->emailService->getEmailThread($printerProblem);

        return response()->json([
            'emails' => $emails->map(fn ($e) => [
                'id'           => $e->id,
                'direction'    => $e->direction,
                'email_type'   => $e->email_type,
                'subject'      => $e->subject,
                'body'         => $e->body,
                'ai_generated' => $e->ai_generated,
                'created_at'   => $e->created_at->format('d.m.Y H:i'),
            ]),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /printer-problems/{problem}/emails/generate
    // Generate first AI draft (no emails yet)
    // -------------------------------------------------------------------------
    public function generate(int $problem): JsonResponse
    {
        $printerProblem = $this->problemService->findOrFail($problem);

        try {
            $draft = $this->emailService->generateDraft($printerProblem);

            return response()->json([
                'success' => true,
                'email'   => [
                    'subject' => $draft['subject'],
                    'body'    => $draft['body'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'KI-Generierung fehlgeschlagen: ' . $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /printer-problems/{problem}/emails/rewrite
    // Rewrite latest draft based on user remarks
    // -------------------------------------------------------------------------
    public function rewrite(RewriteEmailRequest $request, int $problem): JsonResponse
    {
        $printerProblem = $this->problemService->findOrFail($problem);

        try {
            $draft = $this->emailService->rewriteDraft(
                $printerProblem,
                $request->validated('remarks'),
            );

            return response()->json([
                'success' => true,
                'email'   => [
                    'subject' => $draft['subject'],
                    'body'    => $draft['body'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Umschreiben fehlgeschlagen: ' . $e->getMessage(),
            ], 500);
        }
    }

    // -------------------------------------------------------------------------
    // POST /printer-problems/{problem}/emails/save
    // Save the approved/edited draft as user_edited
    // -------------------------------------------------------------------------
    public function save(SaveEmailDraftRequest $request, int $problem): JsonResponse
    {
        $printerProblem = $this->problemService->findOrFail($problem);

        $email = $this->emailService->saveUserDraft(
            $printerProblem,
            $request->validated('subject') ?? '',
            $request->validated('body'),
        );

        return response()->json([
            'success' => true,
            'message' => 'E-Mail-Entwurf wurde gespeichert.',
            'created_at' => $email->created_at->format('d.m.Y H:i'),
        ]);
    }

    // -------------------------------------------------------------------------
    // POST /printer-problems/{problem}/emails/reply
    // Store incoming manufacturer reply
    // -------------------------------------------------------------------------
    public function storeReply(StoreManufacturerReplyRequest $request, int $problem): JsonResponse
    {
        $printerProblem = $this->problemService->findOrFail($problem);

        $email = $this->emailService->storeManufacturerReply(
            $printerProblem,
            $request->validated('body'),
            $request->validated('subject'),
        );

        return response()->json([
            'success' => true,
            'message' => 'Herstellerantwort wurde gespeichert.',
            'created_at' => $email->created_at->format('d.m.Y H:i'),
        ]);
    }
}