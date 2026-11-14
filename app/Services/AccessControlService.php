<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * ระบบจัดการสิทธิ์การเข้าถึง 3 ชั้น (3-Layer Hybrid RBAC + Per-User Overrides)
 * ปรับแต่งเฉพาะสำหรับระบบเว็บ "หม่าล่าสตรีท (Mala Street)"
 */
class AccessControlService
{
    public const ACTIONS = [
        'view'    => 'ดู',
        'create'  => 'เพิ่ม',
        'edit'    => 'แก้ไข',
        'delete'  => 'ลบ',
        'approve' => 'อนุมัติ',
        'export'  => 'ส่งออก',
    ];

    /**
     * โครงสร้างต้นไม้สิทธิ์ทั้งหมดของระบบหม่าล่าสตรีท (Module -> Child -> Actions)
     */
    public static function getPermissionTree(): array
    {
        return [
            'dashboard' => [
                'label' => 'ภาพรวม (Dashboard)',
                'icon'  => '📊',
                'children' => [
                    'overview' => [
                        'label'       => 'ภาพรวมยอดขายแยกสาขา / ประเภทซุป-ปิ้งย่าง',
                        'description' => 'ดูสรุปยอดขาย ต้นทุน กำไรสุทธิ และสถิติรายวัน-รายเดือน',
                        'actions'     => ['view', 'export'],
                    ],
                ],
            ],
            'pos' => [
                'label' => 'POS (ขายหน้าร้าน, ชั่งน้ำหนัก/นับไม้ & คิดเงิน)',
                'icon'  => '🛒',
                'children' => [
                    'orders' => [
                        'label'       => 'เปิดโต๊ะ / ชั่งกรัม-นับไม้ / คิดเงิน / ยกเลิกบิล (Void)',
                        'description' => 'รับออเดอร์หน้าร้าน ชำระเงินสด/โอน QR และยกเลิกบิล',
                        'actions'     => ['view', 'create', 'edit', 'delete'],
                    ],
                    'tables' => [
                        'label'       => 'จัดการโต๊ะอาหารและคิวลูกค้า',
                        'description' => 'เปิด-ปิดโต๊ะ ย้ายโต๊ะ และจัดการคิวลูกค้าหน้าร้าน',
                        'actions'     => ['view', 'create', 'edit', 'delete'],
                    ],
                    'menu' => [
                        'label'       => 'จัดการเมนูหม่าล่า / ราคาต่อขีด-ต่อไม้ / เปิด-ปิดของหมด',
                        'description' => 'แก้ไขราคาไม้หม่าล่า น้ำซุป และตั้งค่าวัตถุดิบหมดชั่วคราว',
                        'actions'     => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            'kitchen' => [
                'label' => 'จอคิวครัว & ปิ้งย่าง-ต้มหม่าล่า (KDS)',
                'icon'  => '🍳',
                'children' => [
                    'queue' => [
                        'label'       => 'ดูคิวออเดอร์ครัว / กดรับออเดอร์ / อัปเดตสถานะเสิร์ฟ',
                        'description' => 'หน้าจอแสดงคิวอาหารระดับความเผ็ดและแจ้งเตือนเสิร์ฟเรียลไทม์',
                        'actions'     => ['view', 'edit'],
                    ],
                ],
            ],
            'inventory' => [
                'label' => 'คลังสต็อกสินค้า, สแกนใบเสร็จ OCR & สต็อกไม้คงเหลือ',
                'icon'  => '🍢',
                'children' => [
                    'stock' => [
                        'label'       => 'รับเข้า-เบิกใช้วัตถุดิบ & สแกนใบเสร็จตรวจจับราคาขึ้น-ลง',
                        'description' => 'สแกนบิลแม็คโคร/ตลาดสด เช็คดัชนีราคาวัตถุดิบ และนับสต็อกไม้คงเหลือ',
                        'actions'     => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    'waste' => [
                        'label'       => 'บันทึกตัดของเสีย / ไม้ชำรุด / กระทบยอดสต็อกรายวัน',
                        'description' => 'ปรับปรุงยอดไม้คงเหลือจริงและตัดสต็อกของเสียรายวัน',
                        'actions'     => ['view', 'create', 'edit', 'delete', 'approve'],
                    ],
                ],
            ],
            'customers' => [
                'label' => 'ลูกค้าออนไลน์ & สะสมแต้ม (Customer Hub)',
                'icon'  => '👥',
                'children' => [
                    'members' => [
                        'label'       => 'ทะเบียนลูกค้าสมาชิก / ออเดอร์ออนไลน์ / สะสมแต้มแลกฟรี',
                        'description' => 'จัดการออเดอร์สั่งออนไลน์ แชทลูกค้า และระบบสะสมแต้ม',
                        'actions'     => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                ],
            ],
            'finance' => [
                'label' => 'การเงิน, ฟิกคอส & รายรับ-รายจ่าย (Finance & Profit)',
                'icon'  => '💰',
                'children' => [
                    'daily' => [
                        'label'       => 'บันทึกรายจ่ายหน้าร้าน & ฟิกคอส (ค่าวัตถุดิบสด/ค่าเช่า/น้ำไฟ)',
                        'description' => 'บันทึกรายรับ-รายจ่ายประจำวัน และคำนวณกำไรสุทธิ',
                        'actions'     => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    'drawer' => [
                        'label'       => 'ลิ้นชักเงินทอนแคชเชียร์ (ตั้งวงเงินเช้า / เบิกเงินทอนเพิ่ม)',
                        'description' => 'ตรวจสอบยอดเงินสดในลิ้นชักหน้าร้านและเบิกโอนระหว่างกะ',
                        'actions'     => ['view', 'create', 'edit', 'approve'],
                    ],
                    'vault' => [
                        'label'       => 'กระเป๋าเงินหลักประจำสาขา / ปิดยอดรายวัน / โอนเข้าบัญชีร้าน',
                        'description' => 'สรุปยอดปิดกะประจำวันและโอนเงินเข้าบัญชีหลักของร้าน',
                        'actions'     => ['view', 'create', 'edit', 'approve', 'export'],
                    ],
                ],
            ],
            'employees' => [
                'label' => 'พนักงาน, สแกนหน้าเข้างาน & เงินเดือน (HR & Payroll)',
                'icon'  => '🪪',
                'children' => [
                    'attendance' => [
                        'label'       => 'สแกนใบหน้าเข้า-ออกงาน & ตรวจสอบพิกัด GPS ร้าน',
                        'description' => 'ลงเวลาเข้า-ออกงานด้วยกล้อง AI และดูภาพหลักฐานย้อนหลัง',
                        'actions'     => ['view', 'create', 'edit'],
                    ],
                    'payroll' => [
                        'label'       => 'สรุปยอดเงินเดือน, ค่าแรง, OT, KPI & สลิปเงินเดือน',
                        'description' => 'ดูสรุปรายบุคคล คำนวณขาด/ลา/มาสาย และออกใบจ่ายเงินเดือน',
                        'actions'     => ['view', 'edit', 'export'],
                    ],
                ],
            ],
            'branches' => [
                'label' => 'จัดการสาขาหม่าล่าสตรีท',
                'icon'  => '🏢',
                'children' => [
                    'manage' => [
                        'label'       => 'เพิ่ม/แก้ไขสาขา & สลับสาขาที่อนุญาต',
                        'description' => 'จัดการข้อมูลสาขาหลัก สาขาย่อย และกำหนดสิทธิ์ประจำสาขา',
                        'actions'     => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            'access' => [
                'label' => 'การเข้าถึง, สิทธิ์พนักงาน & ระบบรีเซ็ทข้อมูล',
                'icon'  => '🛡️',
                'children' => [
                    'users' => [
                        'label'       => 'จัดการบัญชีพนักงาน ตารางงาน และผูกสาขา',
                        'description' => 'เพิ่ม/แก้ไข/ลบบัญชีพนักงาน ตั้งค่ากะเวลาทำงานและค่าจ้าง',
                        'actions'     => ['view', 'create', 'edit', 'delete'],
                    ],
                    'permissions' => [
                        'label'       => 'กำหนดสิทธิ์การเข้าถึงรายบุคคล & อนุมัติพนักงานใหม่',
                        'description' => 'เปิด-ปิดสิทธิ์การเข้าถึงแต่ละหน้าและอนุมัติคำขอลงทะเบียน',
                        'actions'     => ['view', 'create', 'edit', 'approve'],
                    ],
                    'reset' => [
                        'label'       => 'ระบบรีเซ็ทข้อมูลและกู้คืนจากถังสำรอง 30 วัน',
                        'description' => 'ล้างข้อมูลทดสอบและกู้คืนข้อมูลย้อนหลังภายใน 30 วัน',
                        'actions'     => ['view', 'create', 'delete', 'approve'],
                    ],
                ],
            ],
        ];
    }

    /**
     * ดึงรายการ Permission Keys ทั้งหมดในระบบ
     */
    public static function getAllPermissionKeys(): array
    {
        $keys = [];
        foreach (self::getPermissionTree() as $modKey => $mod) {
            foreach ($mod['children'] as $childKey => $child) {
                foreach ($child['actions'] as $act) {
                    $keys[] = "{$modKey}.{$childKey}.{$act}";
                }
            }
        }
        return $keys;
    }

    /**
     * สิทธิ์เริ่มต้นตามตำแหน่งงาน (Role Default Templates) ของร้านหม่าล่าสตรีท
     */
    public static function getDefaultPermissionsForRole(string $roleName): array
    {
        $normRole = strtolower(trim($roleName));
        if (str_contains($normRole, 'super') || $normRole === 'admin') {
            $normRole = 'admin';
        } elseif (str_contains($normRole, 'manager')) {
            $normRole = 'manager';
        } elseif (str_contains($normRole, 'kitchen') || str_contains($normRole, 'chef')) {
            $normRole = 'kitchen';
        } elseif (str_contains($normRole, 'stock')) {
            $normRole = 'stock';
        } elseif (str_contains($normRole, 'finance') || str_contains($normRole, 'account')) {
            $normRole = 'finance';
        } else {
            $normRole = 'cashier';
        }

        $all = self::getAllPermissionKeys();

        return match ($normRole) {
            'admin' => $all,

            'manager' => array_values(array_filter($all, function (string $key) {
                if (str_starts_with($key, 'access.reset.')) {
                    return false;
                }
                if ($key === 'access.users.delete' || $key === 'branches.manage.delete') {
                    return false;
                }
                return true;
            })),

            'cashier' => [
                'dashboard.overview.view',
                'pos.orders.view', 'pos.orders.create', 'pos.orders.edit',
                'pos.tables.view', 'pos.tables.create', 'pos.tables.edit',
                'pos.menu.view',
                'kitchen.queue.view', 'kitchen.queue.edit',
                'inventory.stock.view',
                'customers.members.view', 'customers.members.create', 'customers.members.edit',
                'finance.daily.view', 'finance.daily.create',
                'finance.drawer.view', 'finance.drawer.create',
                'employees.attendance.view', 'employees.attendance.create',
                'employees.payroll.view',
            ],

            'kitchen' => [
                'kitchen.queue.view', 'kitchen.queue.edit',
                'pos.menu.view', 'pos.menu.edit',
                'inventory.stock.view',
                'inventory.waste.view', 'inventory.waste.create',
                'employees.attendance.view', 'employees.attendance.create',
            ],

            'stock' => [
                'inventory.stock.view', 'inventory.stock.create', 'inventory.stock.edit', 'inventory.stock.export',
                'inventory.waste.view', 'inventory.waste.create', 'inventory.waste.edit',
                'pos.menu.view', 'pos.menu.edit',
                'employees.attendance.view', 'employees.attendance.create',
            ],

            'finance' => [
                'dashboard.overview.view', 'dashboard.overview.export',
                'pos.orders.view',
                'inventory.stock.view', 'inventory.stock.export',
                'customers.members.view', 'customers.members.export',
                'finance.daily.view', 'finance.daily.create', 'finance.daily.edit', 'finance.daily.export',
                'finance.drawer.view', 'finance.drawer.create', 'finance.drawer.edit', 'finance.drawer.approve',
                'finance.vault.view', 'finance.vault.create', 'finance.vault.edit', 'finance.vault.approve', 'finance.vault.export',
                'employees.attendance.view', 'employees.attendance.create',
                'employees.payroll.view', 'employees.payroll.export',
            ],

            default => [
                'pos.orders.view',
                'pos.orders.create',
                'kitchen.queue.view',
                'employees.attendance.view',
                'employees.attendance.create',
            ],
        };
    }

    /**
     * ค้นหาหรือสร้าง User Model จริงจากรหัสพนักงาน/อีเมลในระบบ
     */
    public static function resolveUserModel(User|array|string|int|null $identifier): ?User
    {
        if ($identifier instanceof User) {
            return $identifier;
        }

        if (is_int($identifier) || (is_string($identifier) && ctype_digit($identifier))) {
            return User::find((int) $identifier);
        }

        $email = '';
        $name = 'พนักงาน';
        if (is_array($identifier)) {
            $email = strtolower(trim((string) ($identifier['email'] ?? '')));
            $name = trim((string) ($identifier['name'] ?? 'พนักงาน'));
            if (!$email && !empty($identifier['id']) && is_numeric($identifier['id'])) {
                return User::find((int) $identifier['id']);
            }
        } elseif (is_string($identifier) && $identifier !== '') {
            if (str_contains($identifier, '@')) {
                $email = strtolower(trim($identifier));
            } else {
                $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
                $matched = collect($savedUsers)->firstWhere('id', $identifier);
                if ($matched) {
                    $email = strtolower(trim((string) ($matched['email'] ?? '')));
                    $name = trim((string) ($matched['name'] ?? 'พนักงาน'));
                } elseif ($identifier === 'usr_arena_employee') {
                    $email = 'arena@malastreet.com';
                    $name = 'อารีนา เจ๊ะอามะ';
                } elseif ($identifier === 'usr_main_admin') {
                    $email = 'admin@malastreet.com';
                    $name = 'Admin (เจ้าของร้าน)';
                } elseif (str_starts_with($identifier, 'usr_') && ctype_digit(substr($identifier, 4))) {
                    return User::find((int) substr($identifier, 4));
                }
            }
        }

        if ($email !== '') {
            $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            if (!$user && Schema::hasTable('users')) {
                try {
                    $user = User::create([
                        'name'     => $name ?: 'พนักงาน',
                        'email'    => $email,
                        'password' => bcrypt('password123'),
                    ]);
                } catch (\Throwable $e) {
                    $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
                }
            }
            return $user;
        }

        return null;
    }

    /**
     * คำนวณสิทธิ์ที่ใช้งานได้จริงของผู้ใช้ (Layer 1 + Layer 2 + Layer 3 Overrides)
     */
    public static function getEffectivePermissions(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $profile = Schema::hasTable('staff_profiles') ? $user->staffProfile()->first() : null;
        if ($profile && !$profile->is_active) {
            return [];
        }

        $roleName = $user->getRoleName();
        $defaults = self::getDefaultPermissionsForRole($roleName);

        if (!Schema::hasTable('user_permission_overrides')) {
            return $defaults;
        }

        $overrides = DB::table('user_permission_overrides')
            ->where('user_id', $user->id)
            ->pluck('is_allowed', 'permission_key')
            ->toArray();

        $hasCustomFlag = $profile && !empty($profile->has_custom_permissions);
        if (empty($overrides) && !$hasCustomFlag) {
            return $defaults;
        }

        if ($hasCustomFlag) {
            $allowed = [];
            foreach ($overrides as $key => $isAllowed) {
                if ($isAllowed) {
                    $allowed[] = $key;
                }
            }
            return array_values(array_unique($allowed));
        }

        $effective = array_fill_keys($defaults, true);
        foreach ($overrides as $key => $isAllowed) {
            if ($isAllowed) {
                $effective[$key] = true;
            } else {
                unset($effective[$key]);
            }
        }

        return array_keys($effective);
    }

    /**
     * บันทึกสิทธิ์รายบุคคลและสาขาที่อนุญาตลงฐานข้อมูลจริง (user_permission_overrides + staff_profiles + branch_user)
     */
    public static function saveUserPermissionsAndBranches(User $user, array $permissionKeys, ?array $branchIds = null, ?int $actorId = null): array
    {
        $validAllKeys = self::getAllPermissionKeys();
        $newPermissions = array_values(array_intersect($permissionKeys, $validAllKeys));

        if ($user->getRoleName() === 'admin' && self::isLastSuperAdmin($user->id)) {
            $newPermissions = array_values(array_unique(array_merge($newPermissions, [
                'access.users.view', 'access.users.create', 'access.users.edit',
                'access.permissions.view', 'access.permissions.create', 'access.permissions.edit',
            ])));
        }

        DB::transaction(function () use ($user, $newPermissions, $branchIds, $actorId) {
            if (Schema::hasTable('user_permission_overrides')) {
                DB::table('user_permission_overrides')->where('user_id', $user->id)->delete();

                $now = now();
                $rows = [];
                foreach ($newPermissions as $permKey) {
                    $rows[] = [
                        'user_id'        => $user->id,
                        'permission_key' => $permKey,
                        'is_allowed'     => true,
                        'updated_by'     => $actorId,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ];
                }
                if (!empty($rows)) {
                    DB::table('user_permission_overrides')->insert($rows);
                }
            }

            if (Schema::hasTable('staff_profiles')) {
                StaffProfile::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'employee_code'          => sprintf('MALA-%03d', $user->id),
                        'has_custom_permissions' => true,
                        'permissions_updated_at' => now(),
                    ]
                );
            }

            if ($branchIds !== null && Schema::hasTable('branch_user')) {
                $cleanBranchIds = collect($branchIds)->map(fn($id) => (int) $id)->filter()->values()->toArray();
                $user->branches()->sync($cleanBranchIds);
            }
        });

        return self::getEffectivePermissions($user->fresh());
    }

    /**
     * ล้างแคชสิทธิ์เก่าทั้งหมดจากตาราง settings (permissions_user_*)
     */
    public static function purgeLegacyPermissionCache(?string $specificUserId = null): void
    {
        if (!Schema::hasTable('settings')) {
            return;
        }

        try {
            if ($specificUserId) {
                DB::table('settings')->where('key', "permissions_user_{$specificUserId}")->delete();
            } else {
                DB::table('settings')->where('key', 'like', 'permissions_user_%')->delete();
            }
        } catch (\Throwable $e) {
            // Ignore if settings table is locked
        }
    }

    public static function userHasPermission(?User $user, string $permissionKey): bool
    {
        if (!$user) {
            return false;
        }
        return in_array($permissionKey, self::getEffectivePermissions($user), true);
    }

    public static function getRoleTierLabel(string $roleName): string
    {
        return match ($roleName) {
            'admin'   => 'Super Admin',
            'manager' => 'Admin',
            default   => 'Staff',
        };
    }

    /**
     * ป้องกัน Super Admin คนสุดท้ายที่ยัง Active เผลอระงับหรือถอดสิทธิ์ตัวเอง
     */
    public static function isLastSuperAdmin(int $userId): bool
    {
        if (!Schema::hasTable('roles') || !Schema::hasTable('staff_profiles')) {
            return $userId === 1;
        }

        $adminRole = DB::table('roles')->where('name', 'admin')->first();
        if (!$adminRole) {
            return $userId === 1;
        }

        $activeAdminIds = DB::table('staff_profiles')
            ->where('role_id', $adminRole->id)
            ->where('is_active', true)
            ->pluck('user_id')
            ->toArray();

        if (!in_array($userId, $activeAdminIds)) {
            return false;
        }

        return count($activeAdminIds) <= 1;
    }

    /**
     * ลบพนักงานอย่างปลอดภัย โดยโอนประวัติบิล/รายจ่ายเก่าไปให้ Admin หลักก่อนลบ
     */
    public static function reassignAndDeleteUser(User $targetUser, int $fallbackAdminId): void
    {
        DB::transaction(function () use ($targetUser, $fallbackAdminId) {
            $uid = $targetUser->id;

            $fkMappings = [
                'orders'                => ['created_by', 'cashier_id', 'updated_by'],
                'expenses'              => ['recorded_by', 'created_by', 'approved_by'],
                'incomes'               => ['recorded_by', 'created_by'],
                'treasury_transactions' => ['performed_by', 'approved_by'],
                'stock_movements'       => ['created_by', 'performed_by'],
                'data_reset_snapshots'  => ['performed_by', 'restored_by'],
                'audit_logs'            => ['user_id'],
            ];

            foreach ($fkMappings as $table => $columns) {
                if (Schema::hasTable($table)) {
                    foreach ($columns as $col) {
                        if (Schema::hasColumn($table, $col)) {
                            DB::table($table)->where($col, $uid)->update([$col => $fallbackAdminId]);
                        }
                    }
                }
            }

            if (Schema::hasTable('user_permission_overrides')) {
                DB::table('user_permission_overrides')->where('user_id', $uid)->delete();
            }
            if (Schema::hasTable('branch_user')) {
                DB::table('branch_user')->where('user_id', $uid)->delete();
            }
            if (Schema::hasTable('staff_profiles')) {
                DB::table('staff_profiles')->where('user_id', $uid)->delete();
            }
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $uid)->delete();
            }

            $targetUser->delete();
        });
    }

    /**
     * จับคู่ชื่อ Route ของเว็บหม่าล่าสตรีท เข้ากับรหัสสิทธิ์ที่ต้องใช้
     */
    public static function getRoutePermissionRequirement(?string $routeName): ?array
    {
        if (!$routeName) {
            return null;
        }

        $map = [
            // 1. แดชบอร์ดภาพรวมร้าน
            'dashboard'                        => ['key' => 'dashboard.overview.view', 'label' => 'ภาพรวมร้าน (Dashboard)'],
            'admin.dashboard'                  => ['key' => 'dashboard.overview.view', 'label' => 'ภาพรวมร้าน (Dashboard)'],
            'admin.settings.toggle_online'     => ['key' => 'pos.menu.edit',           'alt_key' => 'dashboard.overview.view', 'label' => 'เปิด-ปิดรับออเดอร์ออนไลน์'],

            // 2. หน้าขาย POS หม่าล่า
            'pos.index'                        => ['key' => 'pos.orders.view',         'label' => 'แคชเชียร์หน้าร้าน (POS)'],
            'pos.store'                        => ['key' => 'pos.orders.create',       'label' => 'เปิดบิลขายหม่าล่า'],
            'pos.order.store'                  => ['key' => 'pos.orders.create',       'label' => 'เปิดบิลขายหม่าล่า'],
            'pos.order.status'                 => ['key' => 'pos.orders.edit',         'alt_key' => 'kitchen.queue.edit', 'label' => 'อัปเดตสถานะออเดอร์'],
            'pos.void'                         => ['key' => 'pos.orders.delete',       'label' => 'ยกเลิกบิลขาย (Void)'],
            'pos.menu.index'                   => ['key' => 'pos.menu.view',           'label' => 'จัดการเมนูหม่าล่า'],

            // 3. จอคิวครัว (KDS)
            'pos.kitchen'                      => ['key' => 'kitchen.queue.view',      'label' => 'จอครัว (KDS)'],
            'pos.kitchen.chat'                 => ['key' => 'kitchen.queue.view',      'alt_key' => 'pos.orders.view', 'label' => 'แชทครัว & หน้าร้าน'],
            'pos.kitchen.chat.send'            => ['key' => 'kitchen.queue.view',      'alt_key' => 'pos.orders.view', 'label' => 'ส่งข้อความแชทครัว'],
            'pos.kitchen.chat.messages'        => ['key' => 'kitchen.queue.view',      'alt_key' => 'pos.orders.view', 'label' => 'ข้อความแชทครัว'],
            'kitchen.index'                    => ['key' => 'kitchen.queue.view',      'label' => 'จอครัว (KDS)'],
            'kitchen.update'                   => ['key' => 'kitchen.queue.edit',      'label' => 'อัปเดตสถานะอาหาร'],

            // 4. สต็อกสินค้า, สแกนใบเสร็จ OCR & สต็อกไม้คงเหลือ
            'admin.receipts.index'             => ['key' => 'inventory.stock.view',    'label' => 'สแกนใบเสร็จ & คลังสต็อกสินค้า'],
            'admin.receipts.store'             => ['key' => 'inventory.stock.create',  'label' => 'บันทึกใบเสร็จวัตถุดิบ'],
            'admin.receipts.destroy'           => ['key' => 'inventory.stock.delete',  'label' => 'ลบใบเสร็จวัตถุดิบ'],
            'admin.receipts.upload_image'      => ['key' => 'inventory.stock.create',  'label' => 'อัปโหลดรูปใบเสร็จ'],
            'admin.receipts.scan_ocr'          => ['key' => 'inventory.stock.create',  'label' => 'สแกนใบเสร็จ AI OCR'],
            'admin.receipts.price_tracker'     => ['key' => 'inventory.stock.view',    'label' => 'สต๊อกสินค้า & ตรวจจับราคา'],
            'admin.receipts.price_tracker.store_material'   => ['key' => 'inventory.stock.create', 'label' => 'เพิ่มรายการวัตถุดิบ'],
            'admin.receipts.price_tracker.update_material'  => ['key' => 'inventory.stock.edit',   'label' => 'แก้ไขรายการวัตถุดิบ'],
            'admin.receipts.price_tracker.destroy_material' => ['key' => 'inventory.stock.delete', 'label' => 'ลบรายการวัตถุดิบ'],
            'admin.receipts.price_tracker.batch_delete'     => ['key' => 'inventory.stock.delete', 'label' => 'ลบรายการวัตถุดิบ'],
            'admin.receipts.price_tracker.destroy_materials'=> ['key' => 'inventory.stock.delete', 'label' => 'ลบรายการวัตถุดิบ'],
            'admin.inventory.index'            => ['key' => 'inventory.stock.view',    'alt_key' => 'inventory.waste.view', 'label' => 'สต็อกไม้คงเหลือ'],
            'admin.inventory.adjust'           => ['key' => 'inventory.stock.edit',    'alt_key' => 'inventory.stock.create', 'label' => 'ปรับยอดสต็อกไม้'],
            'admin.inventory.reconcile'        => ['key' => 'inventory.waste.edit',    'alt_key' => 'inventory.stock.edit', 'label' => 'กระทบยอดสต็อกไม้'],
            'inventory.index'                  => ['key' => 'inventory.stock.view',    'label' => 'คลังสต็อกวัตถุดิบ'],
            'inventory.waste'                  => ['key' => 'inventory.waste.view',    'label' => 'บันทึกของเสีย'],

            // 5. ลูกค้า & สมาชิก
            'customers.index'                  => ['key' => 'customers.members.view',  'label' => 'ทะเบียนสมาชิก'],

            // 6. การเงิน, ฟิกคอส & รายรับ-รายจ่าย
            'admin.expenses.index'             => ['key' => 'finance.daily.view',      'alt_key' => 'finance.drawer.view', 'label' => 'ฟิกคอส & รายรับ-รายจ่าย'],
            'admin.expenses.store'             => ['key' => 'finance.daily.create',    'alt_key' => 'finance.daily.edit', 'label' => 'บันทึกรายจ่าย'],
            'admin.expenses.destroy'           => ['key' => 'finance.daily.delete',    'label' => 'ลบรายจ่าย'],
            'finance.index'                    => ['key' => 'finance.daily.view',      'alt_key' => 'finance.drawer.view', 'label' => 'รายรับ-รายจ่ายร้าน'],
            'treasury.index'                   => ['key' => 'finance.vault.view',      'alt_key' => 'finance.drawer.view', 'label' => 'กระเป๋าเงิน & ลิ้นชัก'],

            // 7. พนักงาน, สแกนหน้าเข้างาน & เงินเดือน
            'admin.employees.index'            => ['key' => 'employees.attendance.view', 'alt_key' => 'employees.payroll.view', 'label' => 'พนักงาน & ลงเวลาทำงาน'],
            'admin.employees.clock_in'         => ['key' => 'employees.attendance.create', 'alt_key' => 'employees.attendance.view', 'label' => 'สแกนหน้าเข้า-ออกงาน'],
            'admin.employees.update_location'  => ['key' => 'employees.attendance.edit', 'label' => 'ตั้งค่าพิกัดร้าน'],
            'admin.employees.payslip'          => ['key' => 'employees.payroll.view',  'alt_key' => 'employees.attendance.view', 'label' => 'ดูสลิปเงินเดือน'],

            // 8. สาขาและสิทธิ์การเข้าถึง
            'branches.index'                   => ['key' => 'branches.manage.view',    'label' => 'จัดการสาขา'],
            'access.index'                     => ['key' => 'access.users.view',       'alt_key' => 'access.permissions.view', 'label' => 'การเข้าถึง & สิทธิ์การใช้งาน'],
            'admin.access.index'               => ['key' => 'access.users.view',       'alt_key' => 'access.permissions.view', 'label' => 'การเข้าถึง & สิทธิ์การใช้งาน'],
            'admin.access.save'                => ['key' => 'access.permissions.edit', 'label' => 'บันทึกสิทธิ์การเข้าถึง'],
            'admin.access.save_employee'       => ['key' => 'access.users.edit',       'alt_key' => 'access.permissions.edit', 'label' => 'บันทึกข้อมูลพนักงาน'],
            'admin.access.store_user'          => ['key' => 'access.users.create',     'alt_key' => 'access.permissions.create', 'label' => 'เพิ่มพนักงานใหม่'],
            'admin.access.delete_user'         => ['key' => 'access.users.delete',     'label' => 'ลบบัญชีพนักงาน'],
            'access.reset.stats'               => ['key' => 'access.reset.view',       'label' => 'ดูสถิติรีเซ็ทข้อมูล'],
            'access.reset.execute'             => ['key' => 'access.reset.delete',     'label' => 'สั่งรีเซ็ทข้อมูล'],
            'access.reset.restore'             => ['key' => 'access.reset.approve',    'label' => 'กู้คืนข้อมูลสำรอง'],
            'access.reset.restore-all'         => ['key' => 'access.reset.approve',    'label' => 'กู้คืนข้อมูลทั้งหมด'],
            'access.reset.purge'               => ['key' => 'access.reset.delete',     'label' => 'ลบถาวรจากถังสำรอง'],
        ];

        return $map[$routeName] ?? null;
    }

    /**
     * หา Route แรกที่พนักงานมีสิทธิ์เข้าใช้งาน
     */
    public static function getFirstAccessibleRoute(User $user): string
    {
        $candidates = [
            'pos.orders.view'           => 'pos.index',
            'kitchen.queue.view'        => 'pos.kitchen',
            'employees.attendance.view' => 'admin.employees.index',
            'dashboard.overview.view'   => 'admin.dashboard',
            'inventory.stock.view'      => 'admin.receipts.price_tracker',
            'finance.daily.view'        => 'admin.expenses.index',
            'access.permissions.view'   => 'admin.access.index',
        ];

        foreach ($candidates as $perm => $routeName) {
            if (self::userHasPermission($user, $perm) && \Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName);
            }
        }

        return route('login');
    }
}
