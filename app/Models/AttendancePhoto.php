<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class AttendancePhoto extends Model
{
    public const RETENTION_MONTHS = 3;

    protected $fillable = [
        'filename',
        'employee_id',
        'action_type',
        'mime_type',
        'image_base64',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    /**
     * Store or update an attendance photo in the database with a 3-month retention expiry.
     */
    public static function storeFromBinary(
        string $filename,
        string $binaryData,
        ?string $employeeId = null,
        string $actionType = 'check_in',
        string $mimeType = 'image/jpeg'
    ): ?self {
        if (!Schema::hasTable('attendance_photos')) {
            return null;
        }

        $now = Carbon::now('Asia/Bangkok');

        return static::updateOrCreate(
            ['filename' => basename($filename)],
            [
                'employee_id' => $employeeId,
                'action_type' => $actionType,
                'mime_type' => $mimeType,
                'image_base64' => base64_encode($binaryData),
                'expires_at' => $now->copy()->addMonths(self::RETENTION_MONTHS),
            ]
        );
    }

    /**
     * Delete all attendance photos whose 3-month retention period has expired.
     */
    public static function pruneExpired(): int
    {
        if (!Schema::hasTable('attendance_photos')) {
            return 0;
        }

        $now = Carbon::now('Asia/Bangkok');
        $expired = static::where('expires_at', '<=', $now)->get(['id', 'filename']);

        if ($expired->isEmpty()) {
            return 0;
        }

        $uploadDir = public_path('uploads/attendance');
        foreach ($expired as $record) {
            $safeName = basename((string) $record->filename);
            if ($safeName !== '' && !str_starts_with($safeName, 'sample_')) {
                $localPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;
                if (is_file($localPath)) {
                    @unlink($localPath);
                }
            }
        }

        return static::whereIn('id', $expired->pluck('id'))->delete();
    }

    /**
     * Run expired photo pruning at most once every 6 hours during normal web requests.
     */
    public static function pruneExpiredThrottled(): int
    {
        try {
            if (!Cache::add('attendance_photos_pruned_recently', true, now()->addHours(6))) {
                return 0;
            }
        } catch (\Throwable $e) {
            // Fall through to prune directly if cache driver is unavailable
        }

        return static::pruneExpired();
    }
}
