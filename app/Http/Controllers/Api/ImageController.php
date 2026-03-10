<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Image\UploadImageRequest;
use App\Services\ImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function __construct(private ImageService $imageService)
    {
    }

    /**
     * Upload image
     */
    public function upload(UploadImageRequest $request): JsonResponse
    {
        $result = $this->imageService->upload(
            $request->file('image'),
            $request->get('type', 'posts')
        );

        return response()->json([
            'status' => true,
            'message' => 'Image uploaded successfully',
            'data' => $result,
        ], 201);
    }

    /**
     * Delete image
     */
    public function destroy(string $filename): JsonResponse
    {
        $deleted = $this->imageService->delete($filename);

        if (!$deleted) {
            return response()->json([
                'status' => false,
                'message' => 'Image not found',
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Image deleted successfully',
        ]);
    }
}
