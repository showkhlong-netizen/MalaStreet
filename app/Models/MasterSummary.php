<?php

namespace App\Models;

use App\Support\ThaiDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class MasterSummary extends Model
{
    protected $table = 'master_summaries';

    protected $fillable = [
        'doc_key',
        'version',
        'payload',
        'updated_by',
        'updated_at_ms',
    ];

    protected $casts = [
        'version' => 'integer',
        'updated_at_ms' => 'integer',
    ];

    /**
     * Get a single Master Summary Document (Materialized View Pattern).
     * If it doesn't exist yet and a builder closure is provided, computes and stores it once.
     */
    public static function getDocument(string $docKey, ?callable $builder = null): array
    {
        try {
            if (!Schema::hasTable('master_summaries')) {
                return $builder ? $builder() : [];
            }

            $doc = static::where('doc_key', $docKey)->first();
            if ($doc && !empty($doc->payload)) {
                $decoded = json_decode($doc->payload, true);
                if (is_array($decoded)) {
                    $decoded['_version'] = $doc->version;
                    $decoded['_updated_by'] = $doc->updated_by;
                    $decoded['_updated_at_ms'] = $doc->updated_at_ms;
                    return $decoded;
                }
            }

            if ($builder) {
                $data = $builder();
                static::updateDocument($docKey, $data, 'System Init');
                return $data;
            }
        } catch (\Throwable $e) {
            if ($builder) {
                return $builder();
            }
        }

        return [];
    }

    /**
     * Atomically update a Master Summary Document and increment its version.
     */
    public static function updateDocument(string $docKey, array $payload, ?string $updatedBy = null): ?self
    {
        try {
            if (!Schema::hasTable('master_summaries')) {
                return null;
            }

            $nowMs = (int) round(microtime(true) * 1000);

            return DB::transaction(function () use ($docKey, $payload, $updatedBy, $nowMs) {
                $doc = static::where('doc_key', $docKey)->lockForUpdate()->first();
                if ($doc) {
                    $doc->version = ((int) $doc->version) + 1;
                    $doc->payload = json_encode($payload, JSON_UNESCAPED_UNICODE);
                    $doc->updated_by = $updatedBy ?? $doc->updated_by;
                    $doc->updated_at_ms = $nowMs;
                    $doc->save();
                    return $doc;
                }

                return static::create([
                    'doc_key' => $docKey,
                    'version' => 1,
                    'payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                    'updated_by' => $updatedBy ?? 'System',
                    'updated_at_ms' => $nowMs,
                ]);
            });
        } catch (\Throwable $e) {
            return null;
        }
    }

    /**
     * Bump global sync state document so all active browsers know immediately what changed and by whom.
     */
    public static function bumpGlobalSync(
        string $module,
        string $action,
        string $actionLabel,
        ?string $targetName = null,
        ?string $userName = null,
        ?string $userRole = null,
        array $meta = []
    ): void {
        try {
            if (!Schema::hasTable('master_summaries')) {
                return;
            }

            $now = ThaiDate::now();
            $nowMs = (int) round(microtime(true) * 1000);
            $thaiTimeStr = ThaiDate::format($now, true, false, true);

            DB::transaction(function () use ($module, $action, $actionLabel, $targetName, $userName, $userRole, $meta, $nowMs, $thaiTimeStr) {
                $doc = static::where('doc_key', 'global_sync_state')->lockForUpdate()->first();
                $currentPayload = $doc ? (json_decode($doc->payload, true) ?: []) : [];
                $newVersion = $doc ? (((int) $doc->version) + 1) : 1;

                $moduleVersions = $currentPayload['module_versions'] ?? [];
                $moduleVersions[$module] = $newVersion;

                $event = [
                    'version' => $newVersion,
                    'module' => $module,
                    'action' => $action,
                    'action_label' => $actionLabel,
                    'target_name' => $targetName ?: '-',
                    'user_name' => $userName ?: 'ผู้ใช้งานระบบ',
                    'user_role' => $userRole ?: 'Admin',
                    'thai_timestamp' => $thaiTimeStr,
                    'timestamp_ms' => $nowMs,
                    'meta' => $meta,
                ];

                $recentEvents = $currentPayload['recent_events'] ?? [];
                array_unshift($recentEvents, $event);
                $recentEvents = array_slice($recentEvents, 0, 20);

                $updatedPayload = [
                    'version' => $newVersion,
                    'module_versions' => $moduleVersions,
                    'last_event' => $event,
                    'recent_events' => $recentEvents,
                    'updated_at_ms' => $nowMs,
                    'thai_timestamp' => $thaiTimeStr,
                ];

                if ($doc) {
                    $doc->version = $newVersion;
                    $doc->payload = json_encode($updatedPayload, JSON_UNESCAPED_UNICODE);
                    $doc->updated_by = $userName ?: 'System';
                    $doc->updated_at_ms = $nowMs;
                    $doc->save();
                } else {
                    static::create([
                        'doc_key' => 'global_sync_state',
                        'version' => 1,
                        'payload' => json_encode($updatedPayload, JSON_UNESCAPED_UNICODE),
                        'updated_by' => $userName ?: 'System',
                        'updated_at_ms' => $nowMs,
                    ]);
                }
            });

            // Also update module-specific materialized summary document
            static::refreshMaterializedModule($module, $userName);
        } catch (\Throwable $e) {
            // Fail gracefully without breaking core transaction
        }
    }

    /**
     * Refresh the Materialized Summary Document for a given module so reads are fast and compact.
     */
    public static function refreshMaterializedModule(string $module, ?string $updatedBy = null): void
    {
        try {
            $today = ThaiDate::now()->toDateString();

            if (in_array($module, ['pos', 'kitchen', 'online', 'dashboard'])) {
                $todayOrdersCount = Order::whereDate('created_at', $today)->count();
                $todayRevenue = (float) Order::whereDate('created_at', $today)
                    ->where('status', '!=', 'cancelled')
                    ->sum('net_amount');
                $pendingKitchen = Order::whereIn('status', ['pending', 'cooking', 'preparing'])->count();

                static::updateDocument('pos_kitchen_master', [
                    'date' => $today,
                    'thai_date' => ThaiDate::format($today),
                    'today_orders_count' => $todayOrdersCount,
                    'today_revenue' => $todayRevenue,
                    'pending_kitchen_count' => $pendingKitchen,
                    'updated_by' => $updatedBy,
                ], $updatedBy);
            }

            if (in_array($module, ['price_tracker', 'receipts', 'inventory'])) {
                $materialsCount = RawMaterial::count();
                $totalStockSticks = (int) RawMaterial::sum('current_stock_sticks');
                $todayReceiptsTotal = (float) Receipt::whereDate('receipt_date', $today)->sum('total_amount');

                static::updateDocument('inventory_price_master', [
                    'date' => $today,
                    'thai_date' => ThaiDate::format($today),
                    'materials_count' => $materialsCount,
                    'total_stock_sticks' => $totalStockSticks,
                    'today_receipts_total' => $todayReceiptsTotal,
                    'updated_by' => $updatedBy,
                ], $updatedBy);
            }
        } catch (\Throwable $e) {
            // Ignore if tables are migrating
        }
    }
}
