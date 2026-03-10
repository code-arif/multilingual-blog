<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageService
{
    const ALLOWED_TYPES = ['posts', 'avatars', 'general'];
    const MAX_SIZE = 5120; // 5MB in KB
    const IMAGE_QUALITY = 85;

    public function upload(UploadedFile $file, string $type = 'posts'): array
    {
        $type = in_array($type, self::ALLOWED_TYPES) ? $type : 'general';

        $filename = $this->generateFilename($file);
        $directory = "uploads/{$type}";
        $path = "{$directory}/{$filename}";

        // Process image with Intervention
        $image = Image::make($file);

        // Auto-orient based on EXIF data
        $image->orientate();

        // Resize if too large (max 1920px width)
        if ($image->width() > 1920) {
            $image->resize(1920, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        // Convert to WebP for better compression (if possible)
        $webpFilename = pathinfo($filename, PATHINFO_FILENAME) . '.webp';
        $webpPath = "{$directory}/{$webpFilename}";

        // Save original
        Storage::disk('public')->put($path, $image->encode(null, self::IMAGE_QUALITY));

        // Save WebP version
        try {
            Storage::disk('public')->put($webpPath, $image->encode('webp', self::IMAGE_QUALITY));
            $finalFilename = $webpFilename;
            $finalPath = $webpPath;
        } catch (\Exception $e) {
            $finalFilename = $filename;
            $finalPath = $path;
        }

        return [
            'filename' => $finalFilename,
            'url' => asset("storage/{$finalPath}"),
            'original_filename' => $file->getClientOriginalName(),
            'size' => Storage::disk('public')->size($finalPath),
            'width' => $image->width(),
            'height' => $image->height(),
            'mime_type' => $file->getMimeType(),
            'type' => $type,
        ];
    }

    public function delete(string $filename): bool
    {
        // Search across allowed types
        foreach (self::ALLOWED_TYPES as $type) {
            $path = "uploads/{$type}/{$filename}";
            if (Storage::disk('public')->exists($path)) {
                return Storage::disk('public')->delete($path);
            }
        }
        return false;
    }

    private function generateFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);
        return "{$timestamp}_{$random}_{$basename}.{$extension}";
    }
}
