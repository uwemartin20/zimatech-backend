<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageProcessingService
{
    /**
     * Max dimensions as per spec.
     */
    private const MAX_WIDTH  = 1920;
    private const MAX_HEIGHT = 1920;

    /**
     * WebP quality — balances file size vs. visual quality.
     * 75 reliably hits the <250 KB target for typical photos.
     */
    private const WEBP_QUALITY = 75;

    private ImageManager $manager;

    public function __construct()
    {
        // Uses GD driver. Swap to ImagickDriver::class if Imagick is available.
        $this->manager = new ImageManager(new Driver());
    }

    /**
     * Process an uploaded image file:
     *   1. Read the upload into Intervention Image
     *   2. Scale down to fit within MAX_WIDTH × MAX_HEIGHT (preserving aspect ratio)
     *   3. Encode to WebP at WEBP_QUALITY
     *   4. Write the result to a temp file and return an UploadedFile-compatible path
     *      plus the final encoded content as a string.
     *
     * Returns an array:
     *   [
     *     'contents'  => string,   // WebP binary content — write this to storage
     *     'file_name' => string,   // Original name with .webp extension
     *     'mime_type' => 'image/webp',
     *     'file_size' => int,      // bytes after compression
     *   ]
     */
    public function process(UploadedFile $file): array
    {
        $image = $this->manager->read($file->getRealPath());

        // Scale down only — never upscale
        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->scaleDown(self::MAX_WIDTH, self::MAX_HEIGHT);
        }

        $encoded  = $image->toWebp(self::WEBP_QUALITY);
        $contents = (string) $encoded;

        $baseName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $fileName = $baseName . '.webp';

        return [
            'contents'  => $contents,
            'file_name' => $fileName,
            'mime_type' => 'image/webp',
            'file_size' => strlen($contents),
        ];
    }
}