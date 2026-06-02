<?php

namespace App\Http\Controllers;

use App\Services\AttachmentService;
use App\Services\PrinterProblemService;
use App\Http\Requests\StoreAttachmentRequest;
use App\Models\PrinterProblemAttachment;
use Illuminate\Support\Facades\Response;

class PrinterProblemAttachmentController extends Controller
{
    public function __construct(
        private readonly PrinterProblemService $problemService,
        private readonly AttachmentService     $attachmentService,
    ) {}

    // -------------------------------------------------------------------------
    // POST /printer-problems/{problem}/attachments
    // -------------------------------------------------------------------------
    public function store(StoreAttachmentRequest $request, int $problem)
    {
        $printerProblem = $this->problemService->findOrFail($problem);

        $this->attachmentService->storeMany(
            $printerProblem,
            $request->file('files'),
        );

        return redirect()
            ->route('printer-problems.show', $printerProblem->id)
            ->with('success', 'Dateien wurden erfolgreich hochgeladen.');
    }

    // -------------------------------------------------------------------------
    // GET /printer-problems/{problem}/attachments/{attachment}/download
    // Streams the file to the browser — never exposes the /storage/ path.
    // -------------------------------------------------------------------------
    public function download(int $problem, PrinterProblemAttachment $attachment)
    {
        // Make sure the attachment belongs to the given problem
        abort_if($attachment->problem_id !== $problem, 404);

        $absolutePath = $this->attachmentService->getStoragePath($attachment);

        abort_unless(file_exists($absolutePath), 404, 'Datei nicht gefunden.');

        return Response::download(
            $absolutePath,
            $attachment->file_name,
            ['Content-Type' => $attachment->mime_type],
        );
    }

    // -------------------------------------------------------------------------
    // DELETE /printer-problems/{problem}/attachments/{attachment}
    // -------------------------------------------------------------------------
    public function destroy(int $problem, PrinterProblemAttachment $attachment)
    {
        abort_if($attachment->problem_id !== $problem, 404);

        $this->attachmentService->delete($attachment);

        return redirect()
            ->route('printer-problems.show', $problem)
            ->with('success', 'Anhang wurde gelöscht.');
    }
}