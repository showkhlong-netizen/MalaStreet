<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\AccessControlService;
use App\Support\ThaiDate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccessControlController extends Controller
{
    /**
     * Display the Access Control & Permissions Interface (การเข้าถึง & สิทธิ์การใช้งาน).
     * Matches the design and features in Image 2.
     */
    public function index(Request $request): View
    {
        $currentTab = $request->query('tab', 'permissions'); // 'users', 'permissions', 'autologout', 'register', 'reset'
        $selectedUserId = $request->query('user_id');

        // Initial default mock users (Matches Image 2)
        $defaultMockUsers = [
            [
                'id' => 'usr_001',
                'name' => 'อัฐฮา บู...',
                'full_name' => 'อัฐฮา บูเก๊ะซามี (แอดมินระบบ)',
                'email' => 'autha@malastreet.com',
                'phone' => '081-111-2222',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบสูงสุด',
                'initials' => 'OU',
                'color' => 'bg-blue-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_002',
                'name' => 'แอดมิน...',
                'full_name' => 'แอดมินหน้าร้าน สาขาหลัก',
                'email' => 'admin.front@malastreet.com',
                'phone' => '081-222-3333',
                'role' => 'Admin',
                'role_label' => 'ผู้จัดการร้าน',
                'initials' => 'AD',
                'color' => 'bg-emerald-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_003',
                'name' => 'fakkih',
                'full_name' => 'Fakkih มะดีเยาะ (แคชเชียร์ 1)',
                'email' => 'fakkih@malastreet.com',
                'phone' => '081-333-4444',
                'role' => 'Admin',
                'role_label' => 'แคชเชียร์หน้าร้าน',
                'initials' => 'FA',
                'color' => 'bg-slate-900',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_004',
                'name' => 'KRDN S...',
                'full_name' => 'KRDN Somchai (หัวหน้าเชฟ)',
                'email' => 'krdn@malastreet.com',
                'phone' => '081-444-5555',
                'role' => 'Admin',
                'role_label' => 'หัวหน้าเชฟปิ้งย่าง',
                'initials' => 'KS',
                'color' => 'bg-purple-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขาปัตตานี',
                'is_test' => true,
            ],
            [
                'id' => 'usr_005',
                'name' => 'Show K...',
                'full_name' => 'Show Kitima (ฝ่ายบริการ)',
                'email' => 'show1@malastreet.com',
                'phone' => '081-555-6666',
                'role' => 'Admin',
                'role_label' => 'พนักงานบริการ',
                'initials' => 'SK',
                'color' => 'bg-amber-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_006',
                'name' => 'Show K... 2',
                'full_name' => 'Show K. (พาร์ทไทม์วันหยุด)',
                'email' => 'show2@malastreet.com',
                'phone' => '081-666-7777',
                'role' => 'Admin',
                'role_label' => 'พนักงานพาร์ทไทม์',
                'initials' => 'SK',
                'color' => 'bg-rose-600',
                'status' => 'offline',
                'status_label' => 'ออฟไลน์',
                'branch' => 'สาขาปัตตานี',
                'is_test' => true,
            ],
            [
                'id' => 'usr_test_admin',
                'name' => 'Test',
                'full_name' => 'Test (Admin บัญชีทดสอบระบบ)',
                'email' => 'admin@malastreet.com',
                'phone' => '080-000-0000',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบ (Active)',
                'initials' => 'TE',
                'color' => 'bg-sky-600',
                'status' => 'online',
                'status_label' => 'ใช้งานอยู่',
                'branch' => 'ทุกสาขา',
                'is_test' => true,
            ],
        ];

        // Check if test accounts have been cleared
        $isTestCleared = (bool) Setting::get('access_test_accounts_cleared', false);
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);

        if ($isTestCleared) {
            // ONLY show real accounts created by the user!
            if (!empty($savedUsers)) {
                $users = $savedUsers;
            } else {
                // Real primary accounts including Arena (Matching Image 2)
                $users = [
                    [
                        'id' => 'usr_arena_employee',
                        'name' => 'อารีนา เจ๊ะอามะ',
                        'full_name' => 'อารีนา เจ๊ะอามะ',
                        'email' => 'arena@malastreet.com',
                        'phone' => '082-345-6789',
                        'role' => 'Employee',
                        'role_label' => 'พนักงาน',
                        'position' => 'พนง. บริการประจำ',
                        'initials' => 'อ',
                        'color' => 'bg-[#2563EB]',
                        'avatar' => null,
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'สาขายะลา',
                        'is_test' => false,
                        'created_at' => now()->toDateTimeString(),
                    ],
                    [
                        'id' => 'usr_main_admin',
                        'name' => 'Admin (เจ้าของร้าน)',
                        'full_name' => 'ผู้ดูแลระบบสูงสุด ร้านหม่าล่าสตรีท',
                        'email' => 'admin@malastreet.com',
                        'phone' => '081-999-9999',
                        'role' => 'Admin',
                        'role_label' => 'ผู้ดูแลระบบ (Admin)',
                        'position' => 'ผู้จัดการร้าน',
                        'initials' => 'AD',
                        'color' => 'bg-rose-600',
                        'avatar' => null,
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'ทุกสาขา',
                        'is_test' => false,
                        'created_at' => now()->toDateTimeString(),
                    ],
                ];
            }
        } else {
            // Real primary accounts
            $realBaseUsers = [
                [
                    'id' => 'usr_arena_employee',
                    'name' => 'อารีนา เจ๊ะอามะ',
                    'full_name' => 'อารีนา เจ๊ะอามะ',
                    'email' => 'arena@malastreet.com',
                    'phone' => '082-345-6789',
                    'role' => 'Employee',
                    'role_label' => 'พนักงาน',
                    'position' => 'พนง. บริการประจำ',
                    'initials' => 'อ',
                    'color' => 'bg-[#2563EB]',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'สาขายะลา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ]
            ];
            if (!empty($savedUsers)) {
                $users = array_merge($savedUsers, $defaultMockUsers);
            } else {
                $users = array_merge($realBaseUsers, $defaultMockUsers);
            }
        }

        // Resolve current active user and real online sessions
        $currentActor = AuditLog::resolveCurrentUserActor();
        $currentAuthId = \Illuminate\Support\Facades\Auth::id();
        $currentEmail = strtolower($currentActor['email'] ?? \Illuminate\Support\Facades\Auth::user()?->email ?? '');
        $currentName = strtolower($currentActor['name'] ?? \Illuminate\Support\Facades\Auth::user()?->name ?? '');
        $currentUserId = (string) ($currentActor['id'] ?? ($currentAuthId ? 'usr_' . $currentAuthId : ''));

        $activeEmails = [];
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                $recentSessionUserIds = \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('last_activity', '>=', now()->subMinutes(30)->timestamp)
                    ->whereNotNull('user_id')
                    ->pluck('user_id')
                    ->toArray();
                if (!empty($recentSessionUserIds)) {
                    $activeEmails = User::whereIn('id', $recentSessionUserIds)
                        ->pluck('email')
                        ->map(fn($e) => strtolower($e))
                        ->toArray();
                }
            }
        } catch (\Throwable $e) {}

        if ($currentEmail) {
            $activeEmails[] = $currentEmail;
        }

        // Dynamically compute real status for each user
        foreach ($users as &$u) {
            $uEmail = strtolower($u['email'] ?? '');
            $uName = strtolower($u['name'] ?? '');
            $uId = (string) ($u['id'] ?? '');

            $isCurrent = ($currentUserId && $uId === $currentUserId)
                || ($currentEmail && $uEmail === $currentEmail)
                || ($currentName && $uName === $currentName);

            $isOnline = $isCurrent || in_array($uEmail, $activeEmails);

            $u['is_current'] = $isCurrent;
            $u['status'] = $isOnline ? 'online' : 'offline';
            $u['status_label'] = $isCurrent ? 'ใช้งานอยู่ (คุณ)' : ($isOnline ? 'ออนไลน์' : 'ออฟไลน์');
        }
        unset($u);

        // Active selected user (sync with logged-in user if user_id not explicitly requested)
        if ($selectedUserId) {
            $selectedUser = collect($users)->firstWhere('id', $selectedUserId) ?? ($users[0] ?? null);
        } else {
            $actor = $currentActor;
            $selectedUser = collect($users)->first(function ($u) use ($actor) {
                return ($u['id'] ?? '') === ($actor['id'] ?? '')
                    || strtolower($u['email'] ?? '') === strtolower($actor['email'] ?? '')
                    || ($u['name'] ?? '') === ($actor['name'] ?? '');
            }) ?? ($users[0] ?? null);
        }

        // Purge legacy Setting-based permission cache so everything uses the new AccessControlService & DB overrides
        AccessControlService::purgeLegacyPermissionCache();

        // Build Permission Modules directly from AccessControlService::getPermissionTree()
        $permissionModules = [];
        foreach (AccessControlService::getPermissionTree() as $modKey => $mod) {
            $items = [];
            foreach ($mod['children'] as $childKey => $child) {
                $itemId = "{$modKey}.{$childKey}";
                $actionsMap = [];
                foreach ($child['actions'] as $act) {
                    $actionsMap[$act] = true;
                }
                $items[] = [
                    'id'                => $itemId,
                    'name'              => $child['label'],
                    'description'       => $child['description'] ?? $child['label'],
                    'icon'              => '🔹',
                    'actions'           => $actionsMap,
                    'available_actions' => $child['actions'],
                ];
            }
            $permissionModules[] = [
                'id'    => $modKey,
                'name'  => $mod['label'],
                'badge' => count($items) . ' ระบบย่อย',
                'icon'  => $mod['icon'] ?? '🛡️',
                'items' => $items,
            ];
        }

        // Resolve real User model for selectedUser & load Effective Permissions from DB (user_permission_overrides + role defaults)
        $selectedUserModel = AccessControlService::resolveUserModel($selectedUser);
        $effectiveFlatKeys = $selectedUserModel
            ? AccessControlService::getEffectivePermissions($selectedUserModel)
            : AccessControlService::getDefaultPermissionsForRole($selectedUser['role'] ?? 'Employee');

        $userPermissions = $this->formatFlatKeysToMatrix($effectiveFlatKeys);
        $defaultRolePermissions = $this->formatFlatKeysToMatrix(
            AccessControlService::getDefaultPermissionsForRole($selectedUser['role'] ?? 'Employee')
        );

        // Load branches & selected user's allowed branch IDs
        $allBranches = collect();
        $selectedUserBranchIds = [];
        try {
            if (Schema::hasTable('branches')) {
                $allBranches = Branch::where('status', 'ACTIVE')->orderByDesc('is_main')->orderBy('id')->get();
                if ($selectedUserModel && Schema::hasTable('branch_user')) {
                    $selectedUserBranchIds = $selectedUserModel->branches()->pluck('branches.id')->map(fn($id) => (int) $id)->toArray();
                }
                if (empty($selectedUserBranchIds) && $allBranches->isNotEmpty()) {
                    $selectedUserBranchIds = $allBranches->pluck('id')->map(fn($id) => (int) $id)->toArray();
                }
            }
        } catch (\Throwable $e) {
            $allBranches = collect();
        }

        // Retrieve Employee Extended Data (Schedule, Profile, Payroll, Leaves - Matching Image 2)
        $selectedUserId = $selectedUser['id'] ?? null;
        $empKey = $selectedUserId ? "employee_data_{$selectedUserId}" : null;
        $activeEmployeeData = $empKey ? json_decode(Setting::get($empKey, 'null'), true) : null;

        if (!$activeEmployeeData) {
            $isEmpAdmin = (str_contains(strtolower($selectedUser['role_label'] ?? ''), 'admin') || str_contains(strtolower($selectedUser['role'] ?? ''), 'admin'));
            $empName = $selectedUser['name'] ?? 'พนักงาน';
            $activeEmployeeData = [
                'schedule' => [
                    'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                    'week_filter' => 'all',
                    'time_mode' => 'same',
                    'start_time' => '09:00 AM',
                    'end_time' => '07:00 PM',
                    'notes' => 'ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์',
                ],
                'profile' => [
                    'name' => $empName,
                    'full_name' => $selectedUser['full_name'] ?? $empName,
                    'nickname' => $empName,
                    'position' => $selectedUser['position'] ?? ($isEmpAdmin ? 'ผู้ดูแลระบบ (Admin)' : 'พนง. บริการประจำ'),
                    'job_title' => $isEmpAdmin ? 'ผู้ดูแลระบบ' : 'พนักงานบริการประจำ',
                    'phone' => $selectedUser['phone'] ?? '',
                    'email' => $selectedUser['email'] ?? '',
                    'branch' => $selectedUser['branch'] ?? 'สาขายะลา',
                    'role' => $selectedUser['role'] ?? 'Employee',
                ],
                'payroll' => [
                    'base_salary' => $isEmpAdmin ? 0 : 15000,
                    'ot_rate' => $isEmpAdmin ? 0 : 60,
                    'ot_amount' => 0,
                    'allowance' => $isEmpAdmin ? 0 : 1000,
                    'deduction' => $isEmpAdmin ? 0 : 750,
                    'bank_name' => 'ธนาคารกสิกรไทย',
                    'bank_account' => $isEmpAdmin ? '-' : '123-4-56789-0',
                ],
                'leave' => [
                    'personal_leave_month' => 1,
                    'personal_leave_year' => 6,
                    'personal_leave' => 6,
                    'sick_leave_month' => 1,
                    'sick_leave_year' => 30,
                    'sick_leave' => 30,
                    'annual_leave' => 6,
                    'late_grace_times_month' => 3,
                    'late_grace_minutes' => 15,
                    'late_fine_amount' => 0,
                ],
                'more_info' => [
                    'id_card' => '',
                    'start_date' => '2025-01-01',
                    'emergency_contact' => '',
                    'notes' => $isEmpAdmin ? 'ผู้ดูแลระบบร้านหม่าล่าสตรีท' : 'พนักงานบริการประจำ',
                ],
                'wage_kpi' => [
                    'wage_type' => 'daily',
                    'daily_wage' => $isEmpAdmin ? 0 : 250,
                    'regular_ot_rate' => $isEmpAdmin ? 0 : 50,
                    'ot_per_time' => '',
                    'ot_delivery' => '',
                    'ot_content' => '',
                    'kpi_enabled' => false,
                    'kpi_amount' => 0,
                    'kpi_rounds' => 3,
                    'kpi_period_name' => 'สัปดาห์',
                    'show_in_evaluation' => false,
                ]
            ];
        }

        // Retrieve auto-logout and security settings
        $savedSettings = json_decode(Setting::get('access_auto_logout_settings', '{}'), true) ?: [];
        $autoLogoutSettings = array_merge([
            'timeout_minutes' => (int) Setting::get('access_auto_logout_minutes', 15),
            'timeout_action' => 'lock_screen', // 'lock_screen', 'full_logout', 'quick_pin'
            'exempt_kitchen' => true,
            'exempt_customer' => true,
            'lock_on_blur' => false,
            'warning_countdown' => true,
            'require_pin_shift_close' => true,
            'audit_log' => true,
        ], $savedSettings);

        // Fetch Audit Logs (seed initial system audit logs if empty so Audit tab is immediately populated)
        $auditLogs = collect();
        try {
            if (AuditLog::count() === 0) {
                AuditLog::record(
                    'access',
                    'update',
                    'เปิดใช้งานระบบบันทึกประวัติการทำงาน (Audit Trail & Real-Time Sync พ.ศ. 2569)',
                    'ระบบการเข้าถึง & ความปลอดภัย',
                    'setting',
                    'audit_init'
                );
            }
            $auditLogs = AuditLog::orderByDesc('id')->limit(120)->get();
        } catch (\Throwable $e) {
            $auditLogs = collect();
        }

        $pendingRegistrations = json_decode(Setting::get('pending_register_requests', '[]'), true) ?: [];

        $resetBranches = collect();
        try {
            if (Schema::hasTable('branches')) {
                $resetBranches = Branch::orderByDesc('is_main')->orderBy('status')->orderBy('name')->get();
            }
        } catch (\Throwable $e) {
            $resetBranches = collect();
        }

        return view('admin.access.index', compact(
            'currentTab',
            'users',
            'selectedUser',
            'permissionModules',
            'userPermissions',
            'defaultRolePermissions',
            'isTestCleared',
            'activeEmployeeData',
            'autoLogoutSettings',
            'auditLogs',
            'pendingRegistrations',
            'resetBranches',
            'allBranches',
            'selectedUserBranchIds'
        ));
    }

    /**
     * Save updated permissions matrix and allowed branches for a user directly into user_permission_overrides & branch_user.
     */
    public function save(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        $permissions = $request->input('permissions', []);
        $branchIds = $request->has('branch_ids') ? (array) $request->input('branch_ids', []) : null;

        if (empty($userId)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้งาน'], 422);
        }

        // Purge legacy Setting permission cache
        AccessControlService::purgeLegacyPermissionCache((string) $userId);

        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        $targetUser = collect($savedUsers)->firstWhere('id', $userId);
        $targetUserName = $targetUser['name'] ?? $userId;

        $flatKeys = $this->flattenMatrixToKeys(is_array($permissions) ? $permissions : []);
        $userModel = AccessControlService::resolveUserModel($targetUser ?: $userId);

        if ($userModel) {
            $effective = AccessControlService::saveUserPermissionsAndBranches(
                $userModel,
                $flatKeys,
                $branchIds,
                Auth::id()
            );
        } else {
            $effective = $flatKeys;
        }

        AuditLog::record(
            'access',
            'update',
            "อัปเดตสิทธิ์การเข้าถึงของ {$targetUserName} (" . count($effective) . " สิทธิ์ย่อย)",
            $targetUserName,
            'permission',
            (string) $userId
        );

        return response()->json([
            'success'           => true,
            'message'           => "บันทึกสิทธิ์การเข้าถึงและสาขาของ {$targetUserName} เรียบร้อยแล้ว!",
            'user_id'           => $userId,
            'permissions_count' => count($effective),
            'permissions'       => $effective,
        ]);
    }

    /**
     * แปลง Flat Permission Keys (เช่น ['pos.orders.view', 'pos.orders.create']) เป็น Matrix สำหรับหน้า UI
     */
    protected function formatFlatKeysToMatrix(array $flatKeys): array
    {
        $matrix = [];
        foreach ($flatKeys as $key) {
            if (!is_string($key)) {
                continue;
            }
            $parts = explode('.', $key);
            if (count($parts) === 3) {
                $itemId = "{$parts[0]}.{$parts[1]}";
                $act = $parts[2];
                $matrix[$itemId][$act] = true;
            }
        }
        return $matrix;
    }

    /**
     * แปลงข้อมูลจากหน้า UI (รองรับทั้งแบบ Flat Array และแบบ Matrix Object) เป็นรายการ Permission Keys มาตรฐาน
     */
    protected function flattenMatrixToKeys(array $permissions): array
    {
        $keys = [];
        foreach ($permissions as $k => $v) {
            if (is_int($k) && is_string($v) && substr_count($v, '.') === 2) {
                $keys[] = $v;
            } elseif (is_string($k) && is_array($v)) {
                foreach ($v as $act => $enabled) {
                    if ($enabled) {
                        $normAct = ($act === 'add') ? 'create' : $act;
                        $keys[] = "{$k}.{$normAct}";
                    }
                }
            }
        }
        return array_values(array_unique($keys));
    }

    /**
     * Save employee schedule and profile data (Image 2 - ข้อมูล & สิทธิการทำงานพนักงาน).
     */
    public function saveEmployeeData(Request $request): JsonResponse
    {
        $userId = $request->input('user_id');
        if (empty($userId)) {
            return response()->json(['success' => false, 'message' => 'ไม่พบรหัสผู้ใช้งาน'], 422);
        }

        $existingRaw = Setting::get("employee_data_{$userId}", '');
        $existingData = json_decode($existingRaw ?: '{}', true) ?: [];

        $schedule = $request->input('schedule', $existingData['schedule'] ?? []);
        $profile = $request->input('profile', $existingData['profile'] ?? []);
        if (!is_array($profile)) {
            $profile = [];
        }
        $payroll = $request->has('payroll') ? $request->input('payroll', []) : ($existingData['payroll'] ?? []);
        $leave = $request->input('leave', $existingData['leave'] ?? []);
        $moreInfo = $request->input('more_info', $existingData['more_info'] ?? []);
        $wageKpi = $request->input('wage_kpi', $existingData['wage_kpi'] ?? []);

        // Load access_users_list (seed default primary accounts if empty while test accounts are cleared)
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        $isTestCleared = (bool) Setting::get('access_test_accounts_cleared', false);
        if (empty($savedUsers) && $isTestCleared && in_array($userId, ['usr_arena_employee', 'usr_main_admin'], true)) {
            $savedUsers = [
                [
                    'id' => 'usr_arena_employee',
                    'name' => 'อารีนา เจ๊ะอามะ',
                    'full_name' => 'อารีนา เจ๊ะอามะ',
                    'email' => 'arena@malastreet.com',
                    'phone' => '082-345-6789',
                    'role' => 'Employee',
                    'role_label' => 'พนักงาน',
                    'position' => 'พนง. บริการประจำ',
                    'initials' => 'อ',
                    'color' => 'bg-[#2563EB]',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'สาขายะลา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'usr_main_admin',
                    'name' => 'Admin (เจ้าของร้าน)',
                    'full_name' => 'ผู้ดูแลระบบสูงสุด ร้านหม่าล่าสตรีท',
                    'email' => 'admin@malastreet.com',
                    'phone' => '081-999-9999',
                    'role' => 'Admin',
                    'role_label' => 'ผู้ดูแลระบบ (Admin)',
                    'position' => 'ผู้จัดการร้าน',
                    'initials' => 'AD',
                    'color' => 'bg-rose-600',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'ทุกสาขา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ],
            ];
        }

        $matchedListUser = collect($savedUsers)->firstWhere('id', $userId);
        $fallbackEmails = [
            'usr_arena_employee' => 'arena@malastreet.com',
            'usr_main_admin' => 'admin@malastreet.com',
        ];
        $oldEmail = strtolower(trim((string) ($existingData['profile']['email'] ?? ($matchedListUser['email'] ?? ($fallbackEmails[$userId] ?? '')))));
        $newEmail = strtolower(trim((string) ($profile['email'] ?? $oldEmail)));
        if ($newEmail !== '') {
            $profile['email'] = $newEmail;
        }

        // Handle simple password/PIN update (allows any non-empty length including 1-4 chars; preserves existing if blank)
        $submittedPassword = array_key_exists('password', $profile) ? trim((string) $profile['password']) : '';
        $existingPassword = isset($existingData['profile']['password']) ? (string) $existingData['profile']['password'] : '';
        if (strlen($submittedPassword) > 0) {
            $profile['password'] = $submittedPassword;
            $profile['has_custom_password'] = true;
            $profile['password_updated_at'] = now()->toDateTimeString();
        } elseif (strlen($existingPassword) > 0) {
            $profile['password'] = $existingPassword;
            $profile['has_custom_password'] = true;
            if (!empty($existingData['profile']['password_updated_at'])) {
                $profile['password_updated_at'] = $existingData['profile']['password_updated_at'];
            }
        } else {
            $profile['password'] = '';
        }
        $effectivePassword = (string) ($profile['password'] ?? '');

        $employeeData = [
            'schedule' => $schedule,
            'profile' => $profile,
            'payroll' => $payroll,
            'leave' => $leave,
            'more_info' => $moreInfo,
            'wage_kpi' => $wageKpi,
            'updated_at' => now()->toDateTimeString(),
        ];

        Setting::set("employee_data_{$userId}", json_encode($employeeData), 'employees');

        // Update or create real User in Laravel users table so login uses the new password immediately
        $targetEmail = $newEmail !== '' ? $newEmail : $oldEmail;
        $targetUserName = trim((string) ($profile['name'] ?? ($matchedListUser['name'] ?? $userId)));
        if ($targetEmail !== '') {
            try {
                $userModel = User::whereRaw('LOWER(email) = ?', [$targetEmail])->first();
                if (!$userModel && $oldEmail !== '' && $oldEmail !== $targetEmail) {
                    $userModel = User::whereRaw('LOWER(email) = ?', [$oldEmail])->first();
                }

                if ($userModel) {
                    $userModel->email = $targetEmail;
                    if ($targetUserName !== '') {
                        $userModel->name = $targetUserName;
                    }
                    if (strlen($submittedPassword) > 0) {
                        $userModel->password = Hash::make($submittedPassword);
                    }
                    $userModel->save();
                } else {
                    User::create([
                        'name' => $targetUserName !== '' ? $targetUserName : 'พนักงาน',
                        'email' => $targetEmail,
                        'password' => Hash::make(strlen($effectivePassword) > 0 ? $effectivePassword : 'password123'),
                    ]);
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::warning('saveEmployeeData user sync failed: ' . $e->getMessage());
            }
        }

        // Update profile fields (including email & role) in access_users_list
        $roleLabels = [
            'Admin' => 'ผู้ดูแลระบบ (Admin)',
            'Employee' => 'พนักงานร้าน (Employee)',
            'Customer' => 'ลูกค้า (Customer)',
        ];
        $updated = false;
        foreach ($savedUsers as &$u) {
            if (($u['id'] ?? '') === $userId) {
                if (!empty($profile['name'])) $u['name'] = $profile['name'];
                if (!empty($profile['full_name'])) $u['full_name'] = $profile['full_name'];
                if (!empty($profile['position'])) $u['position'] = $profile['position'];
                if (isset($profile['phone'])) $u['phone'] = $profile['phone'];
                if ($targetEmail !== '') $u['email'] = $targetEmail;
                if (!empty($profile['branch'])) $u['branch'] = $profile['branch'];
                if (!empty($profile['role'])) {
                    $u['role'] = $profile['role'];
                    $u['role_label'] = $roleLabels[$profile['role']] ?? $profile['role'];
                }
                $targetUserName = $u['name'] ?? $targetUserName;
                $updated = true;
                break;
            }
        }
        unset($u);
        if ($updated) {
            Setting::set('access_users_list', json_encode($savedUsers), 'access');
        }

        AuditLog::record(
            'access',
            'update',
            "บันทึกข้อมูลตารางงาน/เงินเดือน/โปรไฟล์พนักงาน: {$targetUserName}",
            $targetUserName,
            'employee_data',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => strlen($submittedPassword) > 0
                ? 'บันทึกรหัสผ่านและข้อมูลพนักงานเรียบร้อยแล้ว!'
                : 'บันทึกข้อมูลตารางงานและสิทธิการทำงานพนักงานเรียบร้อยแล้ว!',
            'data' => $employeeData,
        ]);
    }

    /**
     * Store new real staff, admin, or customer user.
     * Supports image upload, real email & password, phone, role, branch, and test accounts purge.
     */
    public function storeUser(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:50',
            'role' => 'required|string|in:Admin,Employee,Customer',
            'branch' => 'nullable|string|max:100',
            'password' => 'nullable|string|min:1|max:100',
            'avatar' => 'nullable|image|max:5120', // max 5MB
        ]);

        $name = trim($request->input('name'));
        $email = strtolower(trim($request->input('email')));
        $phone = trim($request->input('phone') ?? '-');
        $role = $request->input('role', 'Employee');
        $branch = $request->input('branch', 'ทุกสาขา');
        $rawPassword = trim((string) $request->input('password', ''));
        $password = strlen($rawPassword) > 0 ? $rawPassword : 'password123';
        $clearTestAccounts = $request->boolean('clear_test_accounts', true);

        // Process profile avatar upload
        $avatarUrl = null;
        if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = 'user_' . time() . '_' . Str::random(6) . '.' . $extension;
            $destinationPath = public_path('uploads/users');
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $avatarUrl = asset('uploads/users/' . $filename);
        }

        // Color & Initials
        $initials = mb_substr($name, 0, 2);
        $roleColors = [
            'Admin' => 'bg-rose-600',
            'Employee' => 'bg-blue-600',
            'Customer' => 'bg-emerald-600',
        ];
        $roleLabels = [
            'Admin' => 'ผู้ดูแลระบบ (Admin)',
            'Employee' => 'พนักงานร้าน (Employee)',
            'Customer' => 'ลูกค้า (Customer)',
        ];

        // 1. Create or update real User record in Laravel users table
        try {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                ]
            );
        } catch (\Throwable $e) {
            // Non-blocking fallback
        }

        $userId = 'usr_' . time() . '_' . Str::lower(Str::random(4));
        $newUser = [
            'id' => $userId,
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'role_label' => $roleLabels[$role] ?? $role,
            'initials' => $initials,
            'color' => $roleColors[$role] ?? 'bg-indigo-600',
            'avatar' => $avatarUrl,
            'status' => 'offline',
            'status_label' => 'ออฟไลน์',
            'branch' => $branch,
            'is_test' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        // 2. Retrieve existing saved users and prepend the new user
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        array_unshift($savedUsers, $newUser);

        // 3. Mark test accounts as cleared if requested
        if ($clearTestAccounts) {
            Setting::set('access_test_accounts_cleared', '1', 'access');
        }

        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // 4. Set initial preset permissions in AccessControlService
        AccessControlService::purgeLegacyPermissionCache($userId);

        // 5. Initialize employee extended data (Schedule, Profile, Payroll, Leaves)
        $isNewUserAdmin = ($role === 'Admin');
        $initialEmployeeData = [
            'schedule' => [
                'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                'week_filter' => 'all',
                'time_mode' => 'same',
                'start_time' => '09:00 AM',
                'end_time' => '07:00 PM',
                'notes' => 'ทำงานเฉพาะเสาร์-อาทิตย์, เข้างานตามกะคอนเทนต์',
            ],
            'profile' => [
                'name' => $name,
                'full_name' => $name,
                'nickname' => $name,
                'position' => $isNewUserAdmin ? 'ผู้ดูแลระบบ (Admin)' : 'พนง. บริการประจำ',
                'job_title' => $isNewUserAdmin ? 'ผู้ดูแลระบบ' : 'พนักงานบริการประจำ',
                'phone' => $phone,
                'email' => $email,
                'branch' => $branch,
                'role' => $role,
                'password' => $password,
                'has_custom_password' => $password !== 'password123',
            ],
            'payroll' => [
                'base_salary' => $isNewUserAdmin ? 0 : 15000,
                'ot_rate' => $isNewUserAdmin ? 0 : 60,
                'ot_amount' => 0,
                'allowance' => $isNewUserAdmin ? 0 : 1000,
                'deduction' => $isNewUserAdmin ? 0 : 750,
                'bank_name' => 'ธนาคารกสิกรไทย',
                'bank_account' => $isNewUserAdmin ? '-' : '123-4-56789-0',
            ],
            'leave' => [
                'personal_leave_month' => 1,
                'personal_leave_year' => 6,
                'personal_leave' => 6,
                'sick_leave_month' => 1,
                'sick_leave_year' => 30,
                'sick_leave' => 30,
                'annual_leave' => 6,
                'late_grace_times_month' => 3,
                'late_grace_minutes' => 15,
                'late_fine_amount' => 0,
            ],
            'more_info' => [
                'id_card' => '',
                'start_date' => date('Y-m-d'),
                'emergency_contact' => '',
                'notes' => $isNewUserAdmin ? 'ผู้ดูแลระบบร้านหม่าล่าสตรีท' : 'พนักงานบริการประจำ',
            ],
            'wage_kpi' => [
                'wage_type' => 'daily',
                'daily_wage' => $isNewUserAdmin ? 0 : 250,
                'regular_ot_rate' => $isNewUserAdmin ? 0 : 50,
                'ot_per_time' => '',
                'ot_delivery' => '',
                'ot_content' => '',
                'kpi_enabled' => false,
                'kpi_amount' => 0,
                'kpi_rounds' => 3,
                'kpi_period_name' => 'สัปดาห์',
                'show_in_evaluation' => false,
            ],
            'attendance' => [
                'work_days' => $isNewUserAdmin ? 0 : 26,
                'work_hours' => $isNewUserAdmin ? '0 ชม.' : '208 ชม.',
                'ot_hours' => '0 ชม.',
                'leave_days' => 0,
                'rate' => $isNewUserAdmin ? '0%' : '100%',
                'grade' => 'A',
            ],
            'updated_at' => now()->toDateTimeString(),
        ];
        Setting::set('employee_data_' . $userId, json_encode($initialEmployeeData), 'employees');

        AuditLog::record(
            'access',
            'create',
            "สร้างบัญชีผู้ใช้งานจริงใหม่: {$name} ({$roleLabels[$role]}) สาขา: {$branch}",
            $name,
            'user',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => "สร้างบัญชีจริง '{$name}' ({$roleLabels[$role]}) เรียบร้อยแล้ว! สามารถเข้าใช้งานได้ทันที",
            'user' => $newUser,
            'test_cleared' => $clearTestAccounts,
        ]);
    }

    /**
     * Delete a single user account.
     */
    public function deleteUser(string $id): JsonResponse
    {
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true);
        $deletedUser = collect($savedUsers)->firstWhere('id', $id);
        $deletedName = $deletedUser['name'] ?? $id;

        $savedUsers = array_values(array_filter($savedUsers, fn($u) => ($u['id'] ?? '') !== $id));
        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // If it was one of the default mock accounts, mark test accounts as cleared
        if (str_starts_with($id, 'usr_00') || $id === 'usr_test_admin') {
            Setting::set('access_test_accounts_cleared', '1', 'access');
        }

        AuditLog::record(
            'access',
            'delete',
            "ลบบัญชีผู้ใช้งาน: {$deletedName}",
            $deletedName,
            'user',
            (string) $id
        );

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีผู้ใช้งานเรียบร้อยแล้ว!',
        ]);
    }

    /**
     * Toggle active/inactive status for a user.
     */
    public function toggleStatus(Request $request, string $id): JsonResponse
    {
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        $updated = false;
        $newStatus = 'offline';
        $targetName = $id;

        foreach ($savedUsers as &$u) {
            if ($u['id'] === $id) {
                $currentStatus = $u['status'] ?? 'offline';
                $newStatus = ($currentStatus === 'online') ? 'offline' : 'online';
                $u['status'] = $newStatus;
                $u['status_label'] = ($newStatus === 'online') ? 'ใช้งานอยู่' : 'ออฟไลน์';
                $targetName = $u['name'] ?? $id;
                $updated = true;
                break;
            }
        }
        unset($u);

        if ($updated) {
            Setting::set('access_users_list', json_encode($savedUsers), 'access');

            AuditLog::record(
                'access',
                'status',
                "เปลี่ยนสถานะผู้ใช้งาน {$targetName} เป็น " . ($newStatus === 'online' ? 'ใช้งานอยู่' : 'ออฟไลน์'),
                $targetName,
                ['user_id' => $id, 'status' => $newStatus]
            );
        }

        return response()->json([
            'success' => true,
            'status' => $newStatus,
            'message' => "เปลี่ยนสถานะของ {$targetName} เป็น " . ($newStatus === 'online' ? 'ใช้งานอยู่' : 'ออฟไลน์') . ' เรียบร้อยแล้ว',
        ]);
    }

    /**
     * Clear all test accounts completely.
     */
    public function clearTestUsers(): JsonResponse
    {
        Setting::set('access_test_accounts_cleared', '1', 'access');

        AuditLog::record(
            'access',
            'delete',
            'ล้างบัญชีทดสอบทั้งหมด เหลือเฉพาะบัญชีผู้ใช้งานจริง',
            'บัญชีทดสอบทั้งหมด',
            'user',
            'test_users'
        );

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีทดสอบทั้งหมด (ตามภาพที่ 2) เรียบร้อยแล้ว! ระบบจะแสดงเฉพาะบัญชีจริงเท่านั้น',
        ]);
    }

    /**
     * Update access control general settings (Auto-logout, security policies, backup, etc.).
     */
    public function updateSettings(Request $request): JsonResponse
    {
        $settings = $request->input('settings', []);
        $autoLogoutMinutes = isset($settings['timeout_minutes']) 
            ? (int)$settings['timeout_minutes'] 
            : (int)$request->input('auto_logout_minutes', 15);
        $backupDays = (int) ($settings['backup_days'] ?? $request->input('backup_days', 30));

        Setting::set('access_auto_logout_minutes', (string)$autoLogoutMinutes, 'access');
        Setting::set('access_backup_days', (string)$backupDays, 'access');
        
        if (!empty($settings)) {
            Setting::set('access_auto_logout_settings', json_encode($settings), 'access');
        }

        $label = $autoLogoutMinutes === 0 
            ? 'ปิดใช้งาน' 
            : ($autoLogoutMinutes >= 60 ? ($autoLogoutMinutes / 60) . ' ชม.' : $autoLogoutMinutes . ' นาที');

        AuditLog::record(
            'access',
            'update',
            "อัปเดตตั้งค่าระบบเช็คเอาท์อัตโนมัติ ({$label})",
            "Auto-Logout: {$label}",
            'setting',
            'access_auto_logout'
        );

        return response()->json([
            'success' => true,
            'message' => 'บันทึกการตั้งค่าระบบตัดการเชื่อมต่ออัตโนมัติเรียบร้อยแล้ว!',
            'minutes' => $autoLogoutMinutes,
            'label' => $label,
            'settings' => $settings,
        ]);
    }

    /**
     * Generate default permission preset based on role using AccessControlService.
     */
    protected function getDefaultPermissionsForRole(string $role): array
    {
        return $this->formatFlatKeysToMatrix(AccessControlService::getDefaultPermissionsForRole($role));
    }

    /**
     * Submit a registration from Login Popup.
     * Public endpoint: creates real User, access_users_list entry, employee_data, and permissions for immediate login.
     */
    public function submitRegistrationRequest(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150',
            'password' => 'required|string|min:1|max:100',
            'phone' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:100',
            'role' => 'nullable|string|in:Admin,Employee,Customer',
            'avatar_base64' => 'nullable|string',
        ]);

        $name = trim($request->input('name'));
        $email = strtolower(trim($request->input('email')));
        $phone = trim($request->input('phone') ?? '-');
        $password = trim((string) $request->input('password'));
        $position = trim($request->input('position') ?: 'พนักงานบัญชี');
        $role = $request->input('role') ?: (str_contains($position, 'Admin') || str_contains($position, 'แอดมิน') ? 'Admin' : 'Employee');
        $avatarBase64 = $request->input('avatar_base64');

        // Load existing users list first to check duplicates cleanly
        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        $existsInList = collect($savedUsers)->contains(function ($u) use ($email) {
            return strtolower(trim((string) ($u['email'] ?? ''))) === $email;
        });

        // Check if email already registered in real users or access list
        if (User::whereRaw('LOWER(email) = ?', [$email])->exists() || $existsInList) {
            return response()->json([
                'success' => false,
                'message' => "อีเมล '{$email}' มีอยู่ในระบบแล้ว กรุณาเข้าสู่ระบบ หรือใช้อีเมลอื่น"
            ], 422);
        }

        // Process compressed image and save to permanent file URL
        $avatarUrl = null;
        if (!empty($avatarBase64) && str_starts_with($avatarBase64, 'data:image')) {
            try {
                $destinationPath = public_path('uploads/users');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $parts = explode(',', $avatarBase64);
                $imageData = base64_decode($parts[1] ?? '');
                if ($imageData) {
                    $filename = 'user_req_' . time() . '_' . Str::random(6) . '.jpg';
                    file_put_contents($destinationPath . '/' . $filename, $imageData);
                    $avatarUrl = asset('uploads/users/' . $filename);
                }
            } catch (\Throwable $e) {
                $avatarUrl = $avatarBase64;
            }
        } elseif ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
            try {
                $file = $request->file('avatar');
                $ext = $file->getClientOriginalExtension() ?: 'jpg';
                $filename = 'user_req_' . time() . '_' . Str::random(6) . '.' . $ext;
                $destinationPath = public_path('uploads/users');
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                $avatarUrl = asset('uploads/users/' . $filename);
            } catch (\Throwable $e) {}
        }

        // 1. Create real User record in Laravel users table immediately
        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        // 2. Seed primary accounts if access_users_list was empty while test accounts are cleared
        $isTestCleared = (bool) Setting::get('access_test_accounts_cleared', false);
        if (empty($savedUsers) && $isTestCleared) {
            $savedUsers = [
                [
                    'id' => 'usr_arena_employee',
                    'name' => 'อารีนา เจ๊ะอามะ',
                    'full_name' => 'อารีนา เจ๊ะอามะ',
                    'email' => 'arena@malastreet.com',
                    'phone' => '082-345-6789',
                    'role' => 'Employee',
                    'role_label' => 'พนักงาน',
                    'position' => 'พนง. บริการประจำ',
                    'initials' => 'อ',
                    'color' => 'bg-[#2563EB]',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'สาขายะลา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ],
                [
                    'id' => 'usr_main_admin',
                    'name' => 'Admin (เจ้าของร้าน)',
                    'full_name' => 'ผู้ดูแลระบบสูงสุด ร้านหม่าล่าสตรีท',
                    'email' => 'admin@malastreet.com',
                    'phone' => '081-999-9999',
                    'role' => 'Admin',
                    'role_label' => 'ผู้ดูแลระบบ (Admin)',
                    'position' => 'ผู้จัดการร้าน',
                    'initials' => 'AD',
                    'color' => 'bg-rose-600',
                    'avatar' => null,
                    'status' => 'online',
                    'status_label' => 'ใช้งานอยู่',
                    'branch' => 'ทุกสาขา',
                    'is_test' => false,
                    'created_at' => now()->toDateTimeString(),
                ],
            ];
        }

        $roleLabels = [
            'Admin' => 'ผู้ดูแลระบบ (Admin)',
            'Employee' => 'พนักงานร้าน (Employee)',
            'Customer' => 'ลูกค้า (Customer)',
        ];
        $roleColors = [
            'Admin' => 'bg-rose-600',
            'Employee' => 'bg-blue-600',
            'Customer' => 'bg-emerald-600',
        ];
        $isNewUserAdmin = ($role === 'Admin');

        $userId = 'usr_' . time() . '_' . Str::lower(Str::random(4));
        $newUser = [
            'id' => $userId,
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone !== '' ? $phone : '-',
            'role' => $role,
            'role_label' => $roleLabels[$role] ?? $role,
            'position' => $position,
            'initials' => mb_substr($name, 0, 2),
            'color' => $roleColors[$role] ?? 'bg-blue-600',
            'avatar' => $avatarUrl,
            'status' => 'offline',
            'status_label' => 'ออฟไลน์',
            'branch' => 'ทุกสาขา',
            'is_test' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        array_unshift($savedUsers, $newUser);
        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // 3. Grant default permissions immediately via AccessControlService
        AccessControlService::purgeLegacyPermissionCache($userId);

        // 4. Save employee_data profile & password so admin/access shows everything immediately
        $employeeData = [
            'schedule' => [
                'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                'week_filter' => 'all',
                'time_mode' => 'same',
                'start_time' => '09:00 AM',
                'end_time' => '07:00 PM',
                'notes' => '',
            ],
            'profile' => [
                'name' => $name,
                'full_name' => $name,
                'nickname' => $name,
                'position' => $position,
                'job_title' => $position,
                'phone' => $phone !== '' ? $phone : '-',
                'email' => $email,
                'branch' => 'ทุกสาขา',
                'role' => $role,
                'password' => $password,
                'has_custom_password' => true,
                'password_updated_at' => now()->toDateTimeString(),
            ],
            'payroll' => [
                'base_salary' => $isNewUserAdmin ? 0 : 15000,
                'ot_rate' => $isNewUserAdmin ? 0 : 60,
                'ot_amount' => 0,
                'allowance' => $isNewUserAdmin ? 0 : 1000,
                'deduction' => $isNewUserAdmin ? 0 : 750,
                'bank_name' => 'ธนาคารกสิกรไทย',
                'bank_account' => $isNewUserAdmin ? '-' : '',
            ],
            'leave' => [
                'personal_leave_month' => 1,
                'personal_leave_year' => 6,
                'personal_leave' => 6,
                'sick_leave_month' => 1,
                'sick_leave_year' => 30,
                'sick_leave' => 30,
                'annual_leave' => 6,
                'late_grace_times_month' => 3,
                'late_grace_minutes' => 15,
                'late_fine_amount' => 0,
            ],
            'more_info' => [
                'id_card' => '',
                'start_date' => now()->format('Y-m-d'),
                'emergency_contact' => '',
                'notes' => $position,
            ],
            'wage_kpi' => [
                'wage_type' => 'daily',
                'daily_wage' => $isNewUserAdmin ? 0 : 250,
                'regular_ot_rate' => $isNewUserAdmin ? 0 : 50,
                'ot_per_time' => '',
                'ot_delivery' => '',
                'ot_content' => '',
                'kpi_enabled' => false,
                'kpi_amount' => 0,
                'kpi_rounds' => 3,
                'kpi_period_name' => 'สัปดาห์',
                'show_in_evaluation' => false,
            ],
            'updated_at' => now()->toDateTimeString(),
        ];
        Setting::set("employee_data_{$userId}", json_encode($employeeData), 'employees');

        // Clean any legacy pending request for this email if present
        $pending = json_decode(Setting::get('pending_register_requests', '[]'), true) ?: [];
        if (!empty($pending)) {
            $filteredPending = array_values(array_filter($pending, fn($r) => strtolower(trim((string) ($r['email'] ?? ''))) !== $email));
            if (count($filteredPending) !== count($pending)) {
                Setting::set('pending_register_requests', json_encode($filteredPending), 'access');
            }
        }

        AuditLog::record(
            'access',
            'register_instant',
            "สร้างบัญชีและเปิดใช้งานทันที: {$name} ({$position})",
            $name,
            'user',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => "สร้างบัญชี '{$name}' และบันทึกรหัสผ่านเรียบร้อยแล้ว! สามารถใช้อีเมลและรหัสผ่านนี้เข้าสู่ระบบได้ทันที",
            'user' => $newUser,
            'email' => $email,
        ]);
    }

    /**
     * Approve a pending registration request.
     * Creates real User and Employee profile, grants default role permissions.
     */
    public function approveRegistrationRequest(Request $request, string $id): JsonResponse
    {
        $pending = json_decode(Setting::get('pending_register_requests', '[]'), true) ?: [];
        $targetIndex = null;
        $target = null;
        foreach ($pending as $idx => $r) {
            if (($r['id'] ?? '') === $id) {
                $targetIndex = $idx;
                $target = $r;
                break;
            }
        }

        if (!$target) {
            return response()->json(['success' => false, 'message' => 'ไม่พบคำขอลงทะเบียนนี้ในระบบ'], 404);
        }

        $email = strtolower(trim($target['email']));
        $name = trim($target['name']);
        $password = $target['password'] ?: 'password123';
        $role = $target['role'] ?? 'Employee';
        $position = $target['position'] ?? 'พนักงานบัญชี';
        $avatarUrl = $target['avatar'] ?? null;
        $phone = $target['phone'] ?? '-';

        $roleLabels = [
            'Admin' => 'ผู้ดูแลระบบ (Admin)',
            'Employee' => 'พนักงานร้าน (Employee)',
            'Customer' => 'ลูกค้า (Customer)',
        ];
        $roleColors = [
            'Admin' => 'bg-rose-600',
            'Employee' => 'bg-blue-600',
            'Customer' => 'bg-emerald-600',
        ];

        // 1. Create or update real User record in Laravel users table
        try {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make($password),
                ]
            );
        } catch (\Throwable $e) {}

        // 2. Add to active users list
        $userId = 'usr_' . time() . '_' . Str::lower(Str::random(4));
        $newUser = [
            'id' => $userId,
            'name' => $name,
            'full_name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
            'role_label' => $roleLabels[$role] ?? $role,
            'position' => $position,
            'initials' => mb_substr($name, 0, 2),
            'color' => $roleColors[$role] ?? 'bg-blue-600',
            'avatar' => $avatarUrl,
            'status' => 'offline',
            'status_label' => 'ออฟไลน์',
            'branch' => 'ทุกสาขา',
            'is_test' => false,
            'created_at' => now()->toDateTimeString(),
        ];

        $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        array_unshift($savedUsers, $newUser);
        Setting::set('access_users_list', json_encode($savedUsers), 'access');

        // 3. Grant default role permissions via AccessControlService
        AccessControlService::purgeLegacyPermissionCache($userId);

        // 4. Remove from pending list
        array_splice($pending, $targetIndex, 1);
        Setting::set('pending_register_requests', json_encode($pending), 'access');

        AuditLog::record(
            'access',
            'approve_registration',
            "อนุมัติคำขอสร้างบัญชี: {$name} ({$position})",
            $name,
            'user',
            (string) $userId
        );

        return response()->json([
            'success' => true,
            'message' => "อนุมัติสร้างบัญชี '{$name}' ({$position}) เรียบร้อยแล้ว! พนักงานสามารถเข้าสู่ระบบได้ทันที",
            'user' => $newUser,
            'remaining_count' => count($pending)
        ]);
    }

    /**
     * Reject and delete a pending registration request.
     */
    public function rejectRegistrationRequest(Request $request, string $id): JsonResponse
    {
        $pending = json_decode(Setting::get('pending_register_requests', '[]'), true) ?: [];
        $target = collect($pending)->firstWhere('id', $id);
        $name = $target['name'] ?? $id;

        $filtered = array_values(array_filter($pending, fn($r) => ($r['id'] ?? '') !== $id));
        Setting::set('pending_register_requests', json_encode($filtered), 'access');

        AuditLog::record(
            'access',
            'reject_registration',
            "ปฏิเสธคำขอลงทะเบียน: {$name}",
            $name,
            'pending_registration',
            $id
        );

        return response()->json([
            'success' => true,
            'message' => "ปฏิเสธและลบคำขอลงทะเบียนของ '{$name}' เรียบร้อยแล้ว",
            'remaining_count' => count($filtered)
        ]);
    }
}

