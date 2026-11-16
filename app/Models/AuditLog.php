<?php

namespace App\Models;

use App\Support\ThaiDate;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AuditLog extends Model
{
    protected $table = 'audit_logs';

    protected $fillable = [
        'user_id',
        'user_name',
        'user_role',
        'branch',
        'module',
        'action',
        'action_label',
        'target_name',
        'details',
        'ip_address',
    ];

    /**
     * Resolve currently authenticated user details across Laravel Auth and Access Control profiles.
     */
    public static function resolveCurrentUserActor(): array
    {
        try {
            $user = Auth::user();
            $sessionProfile = session('auth_user_profile');

            if ($sessionProfile && is_array($sessionProfile) && !empty($sessionProfile['name'])) {
                return [
                    'id' => (string) ($sessionProfile['id'] ?? ($user ? 'usr_' . $user->id : 'system')),
                    'name' => (string) $sessionProfile['name'],
                    'role' => (string) ($sessionProfile['role'] ?? 'Admin'),
                    'role_label' => (string) ($sessionProfile['role_label'] ?? ($sessionProfile['role'] === 'Employee' ? 'พนักงานร้าน (Employee)' : 'ผู้ดูแลระบบ (Admin)')),
                    'branch' => (string) ($sessionProfile['branch'] ?? 'สาขายะลา'),
                    'email' => (string) ($sessionProfile['email'] ?? ($user->email ?? '')),
                ];
            }

            if ($user) {
                $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
                $matched = collect($savedUsers)->first(function ($u) use ($user) {
                    return strtolower($u['email'] ?? '') === strtolower($user->email ?? '')
                        || strtolower($u['name'] ?? '') === strtolower($user->name ?? '');
                });

                if ($matched) {
                    return [
                        'id' => (string) ($matched['id'] ?? 'usr_' . $user->id),
                        'name' => (string) ($matched['name'] ?? $user->name),
                        'role' => (string) ($matched['role'] ?? 'Admin'),
                        'role_label' => (string) ($matched['role_label'] ?? 'ผู้ดูแลระบบ (Admin)'),
                        'branch' => (string) ($matched['branch'] ?? 'สาขายะลา'),
                        'email' => (string) ($matched['email'] ?? $user->email),
                    ];
                }

                return [
                    'id' => 'usr_' . $user->id,
                    'name' => $user->name,
                    'role' => 'Admin',
                    'role_label' => 'ผู้ดูแลระบบ (Admin)',
                    'branch' => 'ทุกสาขา',
                    'email' => $user->email,
                ];
            }
        } catch (\Throwable $e) {
            // Fallback
        }

        return [
            'id' => 'guest',
            'name' => request()->is('order*') ? 'ลูกค้าออนไลน์ (Online)' : 'ผู้ใช้งานระบบ',
            'role' => request()->is('order*') ? 'Customer' : 'Staff',
            'role_label' => request()->is('order*') ? 'ลูกค้าสั่งออนไลน์' : 'พนักงาน/แอดมิน',
            'branch' => 'สาขายะลา',
            'email' => '',
        ];
    }

    /**
     * Record an audit log entry and trigger real-time cross-device state synchronization.
     */
    public static function record(
        string $module,
        string $action,
        string $actionLabel,
        ?string $targetName = null,
        mixed $details = [],
        mixed $customActor = null,
        mixed $extraMeta = null
    ): ?self {
        if (!is_array($details)) {
            $details = array_filter([
                'target_type' => (string) $details,
                'target_id' => is_scalar($customActor) ? (string) $customActor : null,
                'meta' => $extraMeta,
            ], fn($v) => $v !== null && $v !== '');
            $customActor = is_array($extraMeta) && isset($extraMeta['name']) ? $extraMeta : null;
        } elseif (!is_array($customActor)) {
            $customActor = null;
        }

        $actor = $customActor ?: static::resolveCurrentUserActor();
        $log = null;

        try {
            if (Schema::hasTable('audit_logs')) {
                $log = DB::transaction(function () use ($actor, $module, $action, $actionLabel, $targetName, $details) {
                    return static::create([
                        'user_id' => mb_substr((string) ($actor['id'] ?? 'system'), 0, 60, 'UTF-8'),
                        'user_name' => mb_substr((string) ($actor['name'] ?? 'ผู้ใช้งานระบบ'), 0, 145, 'UTF-8'),
                        'user_role' => mb_substr((string) ($actor['role'] ?? 'Admin'), 0, 75, 'UTF-8'),
                        'branch' => mb_substr((string) ($actor['branch'] ?? 'สาขายะลา'), 0, 95, 'UTF-8'),
                        'module' => mb_substr($module, 0, 75, 'UTF-8'),
                        'action' => mb_substr($action, 0, 55, 'UTF-8'),
                        'action_label' => mb_substr($actionLabel, 0, 145, 'UTF-8'),
                        'target_name' => mb_substr((string) ($targetName ?: '-'), 0, 250, 'UTF-8'),
                        'details' => !empty($details) ? json_encode($details, JSON_UNESCAPED_UNICODE) : null,
                        'ip_address' => mb_substr((string) (request()->ip() ?? ''), 0, 60, 'UTF-8'),
                    ]);
                });
            }
        } catch (\Throwable $e) {
            // Continue safely via savepoint without aborting any outer PostgreSQL transaction
        }

        // Trigger Materialized View & Real-Time Cross-Device Sync
        MasterSummary::bumpGlobalSync(
            $module,
            $action,
            $actionLabel,
            $targetName,
            $actor['name'] ?? 'ผู้ใช้งานระบบ',
            $actor['role'] ?? 'Admin',
            $details
        );

        return $log;
    }

    /**
     * Helper to get Thai formatted created_at timestamp (พ.ศ.).
     */
    public function getThaiTimestampAttribute(): string
    {
        return ThaiDate::format($this->created_at, true, false, true);
    }

    public function getThaiDatetimeAttribute(): string
    {
        return ThaiDate::format($this->created_at, true, false, true);
    }

    public function getDescriptionAttribute(): string
    {
        return (string) ($this->attributes['action_label'] ?? '');
    }

    /**
     * Helper to get module display label in Thai.
     */
    public static function moduleLabel(string $module): string
    {
        return match ($module) {
            'pos' => '🛒 หน้าขายหน้าร้าน (POS)',
            'kitchen' => '🍳 หน้าจอครัว (Kitchen KDS)',
            'online' => '🛵 สั่งออนไลน์ (Online Order)',
            'receipts' => '🧾 สแกนใบเสร็จ (Receipts)',
            'price_tracker' => '📈 ตรวจจับราคาวัตถุดิบ (Price Tracker)',
            'inventory' => '🍢 สต็อกคงเหลือ (Inventory)',
            'expenses' => '💰 รายรับ-รายจ่าย (Expenses)',
            'employees' => '👥 พนักงาน & เข้างาน (Employees)',
            'access' => '🛡️ สิทธิ์การเข้าถึง (Access Control)',
            'dashboard' => '📊 แดชบอร์ด (Dashboard)',
            'auth' => '🔐 เข้าสู่ระบบ/ออกจากระบบ (Auth)',
            default => $module,
        };
    }
}
