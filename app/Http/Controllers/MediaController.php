<?php

namespace App\Http\Controllers;

use App\Models\UploadedMedia;
use Symfony\Component\HttpFoundation\Response;

class MediaController extends Controller
{
    public function show(string $path): Response
    {
        return $this->serveFromKey($path);
    }

    public function customIngredient(string $filename): Response
    {
        $publicFile = public_path('assets/ingredients/custom/' . $filename);
        if (is_file($publicFile)) {
            return response()->file($publicFile);
        }

        return $this->serveFromKey('assets/ingredients/custom/' . $filename);
    }

    public function uploadFallback(string $folder, string $filename): Response
    {
        $publicFile = public_path("uploads/{$folder}/{$filename}");
        if (is_file($publicFile)) {
            return response()->file($publicFile);
        }

        return $this->serveFromKey("uploads/{$folder}/{$filename}");
    }

    private function serveFromKey(string $rawPath): Response
    {
        $safePath = ltrim(str_replace(['..', '\\'], ['', '/'], $rawPath), '/');
        $cachePath = storage_path('app/media_cache/' . $safePath);

        if (is_file($cachePath)) {
            return response()->file($cachePath, [
                'Cache-Control' => 'public, max-age=31536000, immutable',
            ]);
        }

        $media = UploadedMedia::findByKey($safePath);
        if (!$media || empty($media->data_base64)) {
            abort(404);
        }

        $binary = base64_decode($media->data_base64, true);
        if ($binary === false) {
            $binary = base64_decode($media->data_base64);
        }
        if ($binary === false) {
            abort(404);
        }

        // Re-warm container disk cache after redeploy/restart
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        @file_put_contents($cachePath, $binary);

        return response($binary, 200, [
            'Content-Type' => $media->mime_type ?: 'image/jpeg',
            'Content-Length' => (string) strlen($binary),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
