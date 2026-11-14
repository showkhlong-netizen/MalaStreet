<?php

namespace App\Services;

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
        'view'    => 'ดูข้อมูล',
        'create'  => 'สร้าง/เพิ่ม',
        'edit'    => 'แก้ไข',
        'delete'  => 'ลบ/ยกเลิกบิล',
        'approve' => 'อนุมัติ',
        'export'  => 'ส่งออกรายงาน',
    ];

    /**
     * โครงสร้างต้นไม้สิทธิ์ทั้งหมดของระบบหม่าล่าสตรีท (Module -> Child -> Actions)
     */
    public static function getPermissionTree(): array
    {
        return [
            'dashboard' => [
                'label' => 'แดชบอร์ดยอดขายหม่าล่า',
                'icon'  => 'layout-dashboard',
                'children' => [
                    'overview' => [
                        'label'   => 'ภาพรวมยอดขายแยกสาขา / ประเภทซุป-ปิ้งย่าง',
                        'actions' => ['view', 'export'],
                    ],
                ],
            ],
            'pos' => [
                'label' => 'ขายหน้าร้าน (POS หม่าล่า)',
                'icon'  => 'shopping-bag',
                'children' => [
                    'orders' => [
                        'label'   => 'เปิดโต๊ะ / ชั่งกรัม-นับไม้ / คิดเงิน / ยกเลิกบิล (Void)',
                        'actions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    'tables' => [
                        'label'   => 'จัดการโต๊ะอาหารและคิวลูกค้า',
                        'actions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    'menu' => [
                        'label'   => 'จัดการเมนูหม่าล่า / ราคาต่อขีด-ต่อไม้ / เปิด-ปิดของหมด',
                        'actions' => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            'kitchen' => [
                'label' => 'จอคิวครัว & ปิ้งย่าง-ต้มหม่าล่า (KDS)',
                'icon'  => 'flame',
                'children' => [
                    'queue' => [
                        'label'   => 'ดูคิวออเดอร์ครัว / กดรับออเดอร์ / อัปเดตสถานะเสิร์ฟ',
                        'actions' => ['view', 'edit'],
                    ],
                ],
            ],
            'inventory' => [
                'label' => 'คลังสต็อกวัตถุดิบ & ของเสีย',
                'icon'  => 'package',
                'children' => [
                    'stock' => [
                        'label'   => 'รับเข้า-เบิกใช้วัตถุดิบ (เนื้อสัตว์/ผัก/ลูกชิ้น/น้ำซุป)',
                        'actions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    'waste' => [
                        'label'   => 'บันทึกตัดของเสีย / วัตถุดิบหมดอายุรายวัน',
                        'actions' => ['view', 'create', 'edit', 'delete', 'approve'],
                    ],
                ],
            ],
            'customers' => [
                'label' => 'สมาชิก & สะสมแต้ม',
                'icon'  => 'users',
                'children' => [
                    'members' => [
                        'label'   => 'ทะเบียนลูกค้าสมาชิก / สะสมแต้ม / แลกส่วนลด',
                        'actions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                ],
            ],
            'finance' => [
                'label' => 'รายรับ-รายจ่าย & กระเป๋าเงินร้าน',
                'icon'  => 'wallet',
                'children' => [
                    'daily' => [
                        'label'   => 'บันทึกรายจ่ายหน้าร้าน (ค่าวัตถุดิบสด/น้ำแข็ง/แก๊ส) & รายรับอื่น',
                        'actions' => ['view', 'create', 'edit', 'delete', 'export'],
                    ],
                    'drawer' => [
                        'label'   => 'ลิ้นชักเงินทอนแคชเชียร์ (ตั้งวงเงินเช้า / เบิกเงินทอนเพิ่ม)',
                        'actions' => ['view', 'create', 'edit', 'approve'],
                    ],
                    'vault' => [
                        'label'   => 'กระเป๋าเงินหลักประจำสาขา / ปิดยอดรายวัน / โอนเข้าบัญชีร้าน',
                        'actions' => ['view', 'create', 'edit', 'approve', 'export'],
                    ],
                ],
            ],
            'branches' => [
                'label' => 'จัดการสาขาหม่าล่าสตรีท',
                'icon'  => 'git-branch',
                'children' => [
                    'manage' => [
                        'label'   => 'เพิ่ม/แก้ไขสาขา (สาขาหลัก, สาขาย่อย, บูธ/ฟู้ดทรัค)',
                        'actions' => ['view', 'create', 'edit', 'delete'],
                    ],
                ],
            ],
            'access' => [
                'label' => 'จัดการสิทธิ์พนักงาน & ระบบรีเซ็ทข้อมูล',
                'icon'  => 'shield-check',
                'children' => [
                    'users' => [
                        'label'   => 'จัดการบัญชีพนักงานและผูกสาขา',
                        'actions' => ['view', 'create', 'edit', 'delete'],
                    ],
                    'permissions' => [
                        'label'   => 'กำหนดสิทธิ์รายบุคคลและอนุมัติพนักงานใหม่',
                        'actions' => ['view', 'create', 'edit', 'approve'],
                    ],
                    'reset' => [
                        'label'   => 'ระบบรีเซ็ทข้อมูลและกู้คืนจากถังสำรอง 30 วัน',
                        'actions' => ['view', 'create', 'delete', 'approve'],
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
        $all = self::getAllPermissionKeys();

        return match ($roleName) {
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
                'kitchen.queue.view',
                'customers.members.view', 'customers.members.create', 'customers.members.edit',
                'finance.daily.view', 'finance.daily.create',
                'finance.drawer.view', 'finance.drawer.create',
            ],

            'kitchen' => [
                'kitchen.queue.view', 'kitchen.queue.edit',
                'pos.menu.view', 'pos.menu.edit',
                'inventory.stock.view',
                'inventory.waste.view', 'inventory.waste.create',
            ],

            'stock' => [
                'inventory.stock.view', 'inventory.stock.create', 'inventory.stock.edit', 'inventory.stock.export',
                'inventory.waste.view', 'inventory.waste.create', 'inventory.waste.edit',
                'pos.menu.view', 'pos.menu.edit',
            ],

            'finance' => [
                'dashboard.overview.view', 'dashboard.overview.export',
                'pos.orders.view',
                'inventory.stock.view', 'inventory.stock.export',
                'customers.members.view', 'customers.members.export',
                'finance.daily.view', 'finance.daily.create', 'finance.daily.edit', 'finance.daily.export',
                'finance.drawer.view', 'finance.drawer.create', 'finance.drawer.edit', 'finance.drawer.approve',
                'finance.vault.view', 'finance.vault.create', 'finance.vault.edit', 'finance.vault.approve', 'finance.vault.export',
            ],

            default => [
                'pos.orders.view',
                'pos.orders.create',
            ],
        };
    }

    /**
     * คำนวณสิทธิ์ที่ใช้งานได้จริงของผู้ใช้ (Layer 1 + Layer 2 + Layer 3 Overrides)
     */
    public static function getEffectivePermissions(?User $user): array
    {
        if (!$user) {
            return [];
        }

        $profile = Schema::hasTable('staff_profiles') ? $user->staffProfile : null;
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

        if ($hasCustomFlag && !empty($overrides)) {
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
            // แดชบอร์ด
            'dashboard'         => ['key' => 'dashboard.overview.view', 'label' => 'แดชบอร์ดยอดขาย'],
            'admin.dashboard'   => ['key' => 'dashboard.overview.view', 'label' => 'แดชบอร์ดยอดขาย'],
            // หน้าขาย POS หม่าล่า
            'pos.index'         => ['key' => 'pos.orders.view',         'label' => 'หน้าขาย POS หม่าล่า'],
            'pos.store'         => ['key' => 'pos.orders.create',       'label' => 'เปิดบิลขายหม่าล่า'],
            'pos.order.store'   => ['key' => 'pos.orders.create',       'label' => 'เปิดบิลขายหม่าล่า'],
            'pos.void'          => ['key' => 'pos.orders.delete',       'label' => 'ยกเลิกบิลขาย (Void)'],
            'pos.menu.index'    => ['key' => 'pos.menu.view',           'label' => 'จัดการเมนูหม่าล่า'],
            // จอคิวครัว (KDS)
            'pos.kitchen'       => ['key' => 'kitchen.queue.view',      'label' => 'จอคิวออเดอร์ครัว'],
            'kitchen.index'     => ['key' => 'kitchen.queue.view',      'label' => 'จอคิวออเดอร์ครัว'],
            'kitchen.update'    => ['key' => 'kitchen.queue.edit',      'label' => 'อัปเดตสถานะอาหาร'],
            // สต็อกวัตถุดิบ
            'admin.inventory.index' => ['key' => 'inventory.stock.view', 'label' => 'คลังสต็อกวัตถุดิบ'],
            'inventory.index'   => ['key' => 'inventory.stock.view',    'label' => 'คลังสต็อกวัตถุดิบ'],
            'inventory.waste'   => ['key' => 'inventory.waste.view',    'label' => 'บันทึกของเสีย'],
            // สมาชิก
            'customers.index'   => ['key' => 'customers.members.view',  'label' => 'ทะเบียนสมาชิก'],
            // การเงินและลิ้นชัก
            'admin.expenses.index' => ['key' => 'finance.daily.view',   'alt_key' => 'finance.drawer.view', 'label' => 'รายรับ-รายจ่ายร้าน'],
            'finance.index'     => ['key' => 'finance.daily.view',      'alt_key' => 'finance.drawer.view', 'label' => 'รายรับ-รายจ่ายร้าน'],
            'treasury.index'    => ['key' => 'finance.vault.view',      'alt_key' => 'finance.drawer.view', 'label' => 'กระเป๋าเงิน & ลิ้นชัก'],
            // สาขาและสิทธิ์การเข้าถึง
            'branches.index'    => ['key' => 'branches.manage.view',    'label' => 'จัดการสาขา'],
            'access.index'      => ['key' => 'access.users.view',       'alt_key' => 'access.permissions.view', 'label' => 'จัดการสิทธิ์การเข้าถึง'],
            'access.reset.stats'       => ['key' => 'access.reset.view',   'label' => 'ดูสถิติรีเซ็ทข้อมูล'],
            'access.reset.execute'     => ['key' => 'access.reset.delete', 'label' => 'สั่งรีเซ็ทข้อมูล'],
            'access.reset.restore'     => ['key' => 'access.reset.approve','label' => 'กู้คืนข้อมูลสำรอง'],
            'access.reset.restore-all' => ['key' => 'access.reset.approve','label' => 'กู้คืนข้อมูลทั้งหมด'],
            'access.reset.purge'       => ['key' => 'access.reset.delete', 'label' => 'ลบถาวรจากถังสำรอง'],
        ];

        return $map[$routeName] ?? null;
    }

    /**
     * หา Route แรกที่พนักงานมีสิทธิ์เข้าใช้งาน
     */
    public static function getFirstAccessibleRoute(User $user): string
    {
        $candidates = [
            'pos.orders.view'         => 'pos.index',
            'kitchen.queue.view'      => 'pos.kitchen',
            'dashboard.overview.view' => 'admin.dashboard',
            'inventory.stock.view'    => 'admin.inventory.index',
            'finance.daily.view'      => 'admin.expenses.index',
        ];

        foreach ($candidates as $perm => $routeName) {
            if (self::userHasPermission($user, $perm) && \Illuminate\Support\Facades\Route::has($routeName)) {
                return route($routeName);
            }
        }

        return url('/');
    }
}
