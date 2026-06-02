<?php

namespace App\Services;

use App\Domains\PrinterProblems\Enums\AttachmentType;
use App\Models\PrinterProblem;
use App\Models\PrinterProblemAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function __construct(
        private readonly ImageProcessingService $imageProcessor,
    ) {}

    /**
     * Handle one or more uploaded files for a given problem.
     * Returns the created attachment models.
     *
     * @param  UploadedFile[]  $files
     * @return PrinterProblemAttachment[]
     */
    public function storeMany(PrinterProblem $problem, array $files): array
    {
        $attachments = [];

        foreach ($files as $file) {
            $attachments[] = $this->storeSingle($problem, $file);
        }

        return $attachments;
    }

    /**
     * Upload a single file, process if image, store, persist metadata.
     */
    public function storeSingle(PrinterProblem $problem, UploadedFile $file): PrinterProblemAttachment
    {
        $isImage = $this->isImage($file);

        if ($isImage) {
            return $this->storeImage($problem, $file);
        }

        return $this->storePdf($problem, $file);
    }

    /**
     * Delete an attachment — removes the file from storage and the DB record.
     */
    public function delete(PrinterProblemAttachment $attachment): void
    {
        // Remove physical file
        if (Storage::disk('local')->exists($attachment->file_path)) {
            Storage::disk('local')->delete($attachment->file_path);
        }

        $attachment->delete();
    }

    /**
     * Return a signed temporary URL or a streamed response path for downloads.
     * We return the storage path so the controller can stream it.
     */
    public function getStoragePath(PrinterProblemAttachment $attachment): string
    {
        return Storage::disk('local')->path($attachment->file_path);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function storeImage(PrinterProblem $problem, UploadedFile $file): PrinterProblemAttachment
    {
        $processed = $this->imageProcessor->process($file);

        $directory = $this->directory($problem, 'images');
        $filePath  = $directory . '/' . $this->uniqueName($processed['file_name']);

        // Write WebP binary directly to local storage
        Storage::disk('local')->put($filePath, $processed['contents']);

        return PrinterProblemAttachment::create([
            'problem_id'  => $problem->id,
            'type'        => AttachmentType::Image->value,
            'file_name'   => $processed['file_name'],
            'file_path'   => $filePath,
            'mime_type'   => $processed['mime_type'],
            'file_size'   => $processed['file_size'],
            'uploaded_by' => Auth::id(),
        ]);
    }

    private function storePdf(PrinterProblem $problem, UploadedFile $file): PrinterProblemAttachment
    {
        $directory = $this->directory($problem, 'pdfs');
        $fileName  = $this->uniqueName($file->getClientOriginalName());
        $filePath  = $directory . '/' . $fileName;

        Storage::disk('local')->put($filePath, file_get_contents($file->getRealPath()));

        return PrinterProblemAttachment::create([
            'problem_id'  => $problem->id,
            'type'        => AttachmentType::Pdf->value,
            'file_name'   => $file->getClientOriginalName(),
            'file_path'   => $filePath,
            'mime_type'   => $file->getMimeType() ?? 'application/pdf',
            'file_size'   => $file->getSize(),
            'uploaded_by' => Auth::id(),
        ]);
    }

    /**
     * Builds the storage directory path following the spec:
     * problems/{problem_uid}/attachments/{type}/
     */
    private function directory(PrinterProblem $problem, string $type): string
    {
        return 'problems/' . $problem->problem_uid . '/attachments/' . $type;
    }

    /**
     * Prepend a short UUID to prevent filename collisions.
     */
    private function uniqueName(string $originalName): string
    {
        return Str::substr(Str::uuid(), 0, 8) . '_' . $originalName;
    }

    private function isImage(UploadedFile $file): bool
    {
        return in_array(
            strtolower($file->getClientOriginalExtension()),
            ['jpg', 'jpeg', 'png', 'webp'],
            true
        );
    }
}