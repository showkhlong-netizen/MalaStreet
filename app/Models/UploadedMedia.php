<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;

class UploadedMedia extends Model
{
    protected $table = 'uploaded_media';

    protected $fillable = [
        'media_key',
        'mime_type',
        'data_base64',
        'size_bytes',
    ];

    /**
     * Persist raw binary bytes to PostgreSQL/DB Cloud storage and return root-relative /media URL.
     */
    public static function storeBytes(string $mediaKey, string $binaryData, string $mimeType = 'image/jpeg'): string
    {
        $normalizedKey = ltrim(str_replace('\\', '/', trim($mediaKey)), '/');
        $normalizedKey = preg_replace('#^media/#', '', $normalizedKey);

        static::updateOrCreate(
            ['media_key' => $normalizedKey],
            [
                'mime_type' => $mimeType ?: 'image/jpeg',
                'data_base64' => base64_encode($binaryData),
                'size_bytes' => strlen($binaryData),
            ]
        );

        // Cache on disk for fast subsequent reads on warm containers
        $cachePath = storage_path('app/media_cache/' . $normalizedKey);
        $cacheDir = dirname($cachePath);
        if (!is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        @file_put_contents($cachePath, $binaryData);

        return '/media/' . $normalizedKey;
    }

    public static function storeUploadedFile(string $mediaKey, UploadedFile $file): string
    {
        $bytes = (string) file_get_contents($file->getRealPath());
        $mime = $file->getMimeType() ?: 'image/jpeg';

        return static::storeBytes($mediaKey, $bytes, $mime);
    }

    public static function storeBase64(string $folder, string $base64Input, string $filenamePrefix = 'img'): ?string
    {
        $raw = trim($base64Input);
        if ($raw === '') {
            return null;
        }

        $mime = 'image/jpeg';
        $ext = 'jpg';
        if (preg_match('/^data:(image\/([a-zA-Z0-9\+\-\.]+));base64,/', $raw, $matches)) {
            $mime = strtolower($matches[1]);
            $ext = strtolower($matches[2]);
            if ($ext === 'jpeg' || $ext === 'pjpeg') {
                $ext = 'jpg';
                $mime = 'image/jpeg';
            } elseif ($ext === 'svg+xml') {
                $ext = 'svg';
            }
            $raw = substr($raw, strpos($raw, ',') + 1);
        }

        $decoded = base64_decode($raw, true);
        if ($decoded === false) {
            $decoded = base64_decode($raw);
        }
        if ($decoded === false || strlen($decoded) < 20) {
            return null;
        }

        $folder = trim(str_replace('\\', '/', $folder), '/');
        $filename = $filenamePrefix . '_' . time() . '_' . substr(md5(uniqid('', true)), 0, 6) . '.' . $ext;
        $mediaKey = ($folder !== '' ? $folder . '/' : '') . $filename;

        return static::storeBytes($mediaKey, $decoded, $mime);
    }

    public static function findByKey(string $path): ?self
    {
        $normalized = ltrim(str_replace('\\', '/', trim($path)), '/');
        $candidates = array_values(array_unique(array_filter([
            $normalized,
            preg_replace('#^media/#', '', $normalized),
            preg_replace('#^assets/ingredients/custom/#', 'ingredients/', $normalized),
            preg_replace('#^uploads/#', '', $normalized),
        ])));

        return static::whereIn('media_key', $candidates)->first();
    }
}
