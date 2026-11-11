<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\MasterSummary;
use App\Support\ThaiDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SystemSyncController extends Controller
{
    /**
     * Lightweight Real-Time Sync Endpoint (Single Master Summary Document Pattern).
     * Returns current state version, authoritative Thailand server time (พ.ศ.),
     * last user action event, and active user attribution.
     */
    public function syncState(Request $request): JsonResponse
    {
        $now = ThaiDate::now();
        $nowMs = (int) round(microtime(true) * 1000);

        $syncDoc = MasterSummary::getDocument('global_sync_state', function () use ($now, $nowMs) {
            return [
                'version' => 1,
                'module_versions' => [],
                'last_event' => null,
                'recent_events' => [],
                'updated_at_ms' => $nowMs,
                'thai_timestamp' => ThaiDate::format($now, true, false, true),
            ];
        });

        $actor = AuditLog::resolveCurrentUserActor();

        $response = [
            'success' => true,
            'version' => (int) ($syncDoc['version'] ?? $syncDoc['_version'] ?? 1),
            'module_versions' => $syncDoc['module_versions'] ?? [],
            'last_event' => $syncDoc['last_event'] ?? null,
            'server_time_ms' => $nowMs,
            'server_timezone' => 'Asia/Bangkok',
            'thai_date' => ThaiDate::format($now, false),
            'thai_datetime' => ThaiDate::format($now, true, false, true),
            'thai_full_date' => ThaiDate::formatFull($now, true),
            'thai_year_be' => $now->year + 543,
            'current_user' => $actor,
        ];

        // If client requests specific module master summary document
        $requestedModule = $request->query('module');
        if ($requestedModule === 'pos' || $requestedModule === 'kitchen') {
            $response['master_summary'] = MasterSummary::getDocument('pos_kitchen_master');
        } elseif (in_array($requestedModule, ['price_tracker', 'inventory', 'receipts'])) {
            $response['master_summary'] = MasterSummary::getDocument('inventory_price_master');
        }

        return response()->json($response);
    }

    /**
     * Fetch recent Audit Logs for the Access Control Audit tab (Real-time JSON).
     */
    public function auditLogs(Request $request): JsonResponse
    {
        if (!Schema::hasTable('audit_logs')) {
            return response()->json(['success' => true, 'logs' => []]);
        }

        $query = AuditLog::query()->orderByDesc('id');

        if ($request->filled('module') && $request->input('module') !== 'all') {
            $query->where('module', $request->input('module'));
        }

        if ($request->filled('user') && $request->input('user') !== 'all') {
            $query->where('user_name', $request->input('user'));
        }

        if ($request->filled('search')) {
            $q = trim($request->input('search'));
            $query->where(function ($sub) use ($q) {
                $sub->where('user_name', 'like', "%{$q}%")
                    ->orWhere('action_label', 'like', "%{$q}%")
                    ->orWhere('target_name', 'like', "%{$q}%")
                    ->orWhere('module', 'like', "%{$q}%");
            });
        }

        $logs = $query->limit(150)->get()->map(function (AuditLog $log) {
            return [
                'id' => $log->id,
                'user_id' => $log->user_id,
                'user_name' => $log->user_name,
                'user_role' => $log->user_role,
                'branch' => $log->branch,
                'module' => $log->module,
                'module_label' => AuditLog::moduleLabel($log->module),
                'action' => $log->action,
                'action_label' => $log->action_label,
                'description' => $log->action_label,
                'target_name' => $log->target_name,
                'details' => $log->details ? json_decode($log->details, true) : null,
                'ip_address' => $log->ip_address,
                'thai_timestamp' => ThaiDate::format($log->created_at, true, false, true),
                'thai_datetime' => ThaiDate::format($log->created_at, true, false, true),
                'created_at_iso' => $log->created_at?->toIso8601String(),
            ];
        });

        return response()->json([
            'success' => true,
            'logs' => $logs,
        ]);
    }
}
