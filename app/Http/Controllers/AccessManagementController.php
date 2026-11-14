<?php

namespace App\Http\Controllers;

use App\Models\AccessRequest;
use App\Models\Branch;
use App\Models\DataResetSnapshot;
use App\Models\StaffProfile;
use App\Models\User;
use App\Services\AccessControlService;
use App\Services\DataResetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

/**
 * Controller จัดการสิทธิ์การเข้าถึง & ระบบรีเซ็ทข้อมูล (สำรอง 30 วัน)
 * ปรับแต่งเฉพาะสำหรับระบบเว็บ "หม่าล่าสตรีท (Mala Street)"
 */
class AccessManagementController extends Controller
{
    public function index(Request $request)
    {
        $activeTab = $request->query('tab', 'permissions');
        $selectedUserId = (int) $request->query('user_id', 0);

        $roles = Schema::hasTable('roles') ? DB::table('roles')->orderBy('id')->get() : collect();
        $rolesById = $roles->keyBy('id');

        $rawUsers = User::with(['staffProfile', 'branches'])->orderBy('id')->get();
        $usersData = $rawUsers->map(function (User $u) use ($rolesById) {
            $profile = $u->staffProfile;
            $roleObj = ($profile && $profile->role_id && isset($rolesById[$profile->role_id]))
                ? $rolesById[$profile->role_id]
                : $rolesById->firstWhere('name', $u->getRoleName());

            $roleName = $roleObj->name ?? $u->getRoleName();
            return [
                'id'                     => $u->id,
                'name'                   => $u->name,
                'email'                  => $u->email,
                'employee_code'          => $profile->employee_code ?? sprintf('MALA-%03d', $u->id),
                'phone'                  => $profile->phone ?? '-',
                'role_id'                => $roleObj->id ?? null,
                'role_name'              => $roleName,
                'role_label'             => $roleObj->label ?? $u->getRoleLabel(),
                'role_tier'              => AccessControlService::getRoleTierLabel($roleName),
                'is_active'              => $profile ? (bool) $profile->is_active : true,
                'has_custom_permissions' => $profile ? (bool) ($profile->has_custom_permissions ?? false) : false,
                'permissions'            => AccessControlService::getEffectivePermissions($u),
                'default_permissions'    => AccessControlService::getDefaultPermissionsForRole($roleName),
                'branch_ids'             => $u->branches->pluck('id')->toArray(),
                'branches'               => $u->branches->map(fn($b) => ['id' => $b->id, 'name' => $b->name, 'code' => $b->code])->toArray(),
            ];
        })->toArray();

        if ($selectedUserId <= 0 && !empty($usersData)) {
            $selectedUserId = $usersData[0]['id'];
        }

        $permissionTree = AccessControlService::getPermissionTree();
        $branches = Schema::hasTable('branches')
            ? Branch::where('status', 'ACTIVE')->orderByDesc('is_main')->orderBy('name')->get()
            : collect();
        $resetBranches = Schema::hasTable('branches')
            ? Branch::orderByDesc('is_main')->orderBy('status')->orderBy('name')->get()
            : collect();
        $accessRequests = Schema::hasTable('access_requests')
            ? AccessRequest::latest('id')->get()
            : collect();
        $pendingRequestsCount = Schema::hasTable('access_requests')
            ? AccessRequest::where('status', 'PENDING')->count()
            : 0;

        return view('access.index', compact(
            'activeTab',
            'selectedUserId',
            'usersData',
            'roles',
            'branches',
            'resetBranches',
            'permissionTree',
            'accessRequests',
            'pendingRequestsCount'
        ));
    }

    public function getLiveActiveStatus()
    {
        $users = User::with('staffProfile')->orderBy('id')->get()->map(fn(User $u) => [
            'id'        => $u->id,
            'name'      => $u->name,
            'is_active' => $u->staffProfile ? (bool) $u->staffProfile->is_active : true,
        ]);

        return response()->json([
            'success' => true,
            'users'   => $users,
        ]);
    }

    /**
     * บันทึกสิทธิ์รายบุคคลและผูกสาขาของพนักงาน
     */
    public function savePermissions(Request $request, User $user)
    {
        $actor = Auth::user();
        if (!AccessControlService::userHasPermission($actor, 'access.permissions.edit')) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์แก้ไขการเข้าถึง'], 403);
        }

        $validated = $request->validate([
            'permissions'   => ['present', 'array'],
            'permissions.*' => ['string', 'max:120'],
            'branch_ids'    => ['nullable', 'array'],
            'branch_ids.*'  => ['integer', 'exists:branches,id'],
        ]);

        $validAllKeys = AccessControlService::getAllPermissionKeys();
        $newPermissions = array_values(array_intersect($validated['permissions'], $validAllKeys));

        if ($user->getRoleName() === 'admin' && AccessControlService::isLastSuperAdmin($user->id)) {
            $newPermissions = array_values(array_unique(array_merge($newPermissions, [
                'access.users.view', 'access.users.create', 'access.users.edit',
                'access.permissions.view', 'access.permissions.create', 'access.permissions.edit',
            ])));
        }

        DB::transaction(function () use ($user, $newPermissions, $actor, $request, $validated) {
            DB::table('user_permission_overrides')->where('user_id', $user->id)->delete();

            $now = now();
            $rows = [];
            foreach ($newPermissions as $permKey) {
                $rows[] = [
                    'user_id'        => $user->id,
                    'permission_key' => $permKey,
                    'is_allowed'     => true,
                    'updated_by'     => $actor->id,
                    'created_at'     => $now,
                    'updated_at'     => $now,
                ];
            }
            if (!empty($rows)) {
                DB::table('user_permission_overrides')->insert($rows);
            }

            StaffProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'has_custom_permissions' => true,
                    'permissions_updated_at' => $now,
                ]
            );

            if ($request->has('branch_ids')) {
                $branchIds = collect($validated['branch_ids'] ?? [])->map(fn($id) => (int) $id)->filter()->values()->toArray();
                $user->branches()->sync($branchIds);
            }
        });

        $userFresh = $user->fresh(['branches']);
        return response()->json([
            'success'     => true,
            'message'     => "บันทึกสิทธิ์และสาขาของ {$user->name} เรียบร้อยแล้ว",
            'permissions' => AccessControlService::getEffectivePermissions($userFresh),
            'branch_ids'  => $userFresh->branches->pluck('id')->toArray(),
        ]);
    }

    /**
     * รีเซ็ตสิทธิ์กลับเป็นค่าเริ่มต้นตามตำแหน่งงาน (Role Default)
     */
    public function resetPermissions(Request $request, User $user)
    {
        $actor = Auth::user();
        if (!AccessControlService::userHasPermission($actor, 'access.permissions.edit')) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์รีเซ็ตสิทธิ์'], 403);
        }

        DB::transaction(function () use ($user) {
            DB::table('user_permission_overrides')->where('user_id', $user->id)->delete();
            StaffProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'has_custom_permissions' => false,
                    'permissions_updated_at' => null,
                ]
            );
        });

        return response()->json([
            'success'     => true,
            'message'     => "รีเซ็ตสิทธิ์ของ {$user->name} กลับเป็นค่าเริ่มต้นตามตำแหน่งเรียบร้อยแล้ว",
            'permissions' => AccessControlService::getEffectivePermissions($user->fresh()),
        ]);
    }

    /**
     * คัดลอกสิทธิ์จากพนักงานอีกคน
     */
    public function copyPermissions(Request $request, User $user)
    {
        $actor = Auth::user();
        if (!AccessControlService::userHasPermission($actor, 'access.permissions.edit')) {
            return response()->json(['success' => false, 'message' => 'ไม่มีสิทธิ์คัดลอกสิทธิ์'], 403);
        }

        $validated = $request->validate([
            'source_user_id' => ['required', 'integer', 'exists:users,id'],
        ]);

        $sourceUser = User::findOrFail($validated['source_user_id']);
        $sourcePerms = AccessControlService::getEffectivePermissions($sourceUser);

        $request->merge(['permissions' => $sourcePerms]);
        return $this->savePermissions($request, $user);
    }

    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'   => ['required', 'string', 'min:4'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'role_id'    => ['nullable', 'integer'],
            'branch_ids' => ['nullable', 'array'],
        ]);

        $user = DB::transaction(function () use ($validated) {
            $u = User::create([
                'name'     => $validated['name'],
                'email'    => strtolower(trim($validated['email'])),
                'password' => Hash::make($validated['password']),
            ]);

            StaffProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'role_id'       => $validated['role_id'] ?? null,
                    'employee_code' => sprintf('MALA-%03d', $u->id),
                    'phone'         => $validated['phone'] ?? null,
                    'is_active'     => true,
                ]
            );

            if (!empty($validated['branch_ids'])) {
                $u->branches()->sync(array_map('intval', $validated['branch_ids']));
            }

            return $u;
        });

        return response()->json([
            'success' => true,
            'message' => "สร้างบัญชีพนักงาน {$user->name} เรียบร้อยแล้ว",
            'user'    => $user,
        ]);
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => ['required', 'string', 'max:150'],
            'email'      => ['required', 'email', 'max:150', 'unique:users,email,' . $user->id],
            'password'   => ['nullable', 'string', 'min:4'],
            'phone'      => ['nullable', 'string', 'max:40'],
            'role_id'    => ['nullable', 'integer'],
            'branch_ids' => ['nullable', 'array'],
        ]);

        DB::transaction(function () use ($user, $validated, $request) {
            $user->name = $validated['name'];
            $user->email = strtolower(trim($validated['email']));
            if (!empty($validated['password'])) {
                $user->password = Hash::make($validated['password']);
            }
            $user->save();

            StaffProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'role_id' => $validated['role_id'] ?? ($user->staffProfile->role_id ?? null),
                    'phone'   => $validated['phone'] ?? ($user->staffProfile->phone ?? null),
                ]
            );

            if ($request->has('branch_ids')) {
                $user->branches()->sync(array_map('intval', $validated['branch_ids'] ?? []));
            }
        });

        return response()->json([
            'success' => true,
            'message' => "อัปเดตข้อมูล {$user->name} เรียบร้อยแล้ว",
        ]);
    }

    public function toggleUserStatus(Request $request, User $user)
    {
        if (AccessControlService::isLastSuperAdmin($user->id)) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถระงับบัญชี Super Admin คนสุดท้ายได้'], 422);
        }

        $profile = StaffProfile::firstOrCreate(
            ['user_id' => $user->id],
            ['employee_code' => sprintf('MALA-%03d', $user->id), 'is_active' => true]
        );
        $profile->is_active = !$profile->is_active;
        $profile->save();

        return response()->json([
            'success'   => true,
            'is_active' => $profile->is_active,
            'message'   => $profile->is_active ? "เปิดใช้งานบัญชี {$user->name} แล้ว" : "ระงับบัญชี {$user->name} ชั่วคราวแล้ว",
        ]);
    }

    public function deleteUser(Request $request, User $user)
    {
        if (AccessControlService::isLastSuperAdmin($user->id)) {
            return response()->json(['success' => false, 'message' => 'ไม่สามารถลบบัญชี Super Admin คนสุดท้ายได้'], 422);
        }

        $actorId = Auth::id() ?: 1;
        AccessControlService::reassignAndDeleteUser($user, (int) $actorId);

        return response()->json([
            'success' => true,
            'message' => 'ลบบัญชีพนักงานเรียบร้อยแล้ว',
        ]);
    }

    public function approveAccessRequest(Request $request, AccessRequest $accessRequest)
    {
        if ($accessRequest->status !== 'PENDING') {
            return response()->json(['success' => false, 'message' => 'คำขอนี้ถูกดำเนินการไปแล้ว'], 422);
        }

        $user = DB::transaction(function () use ($accessRequest) {
            $u = User::firstOrCreate(
                ['email' => strtolower(trim($accessRequest->email))],
                [
                    'name'     => $accessRequest->name,
                    'password' => Hash::make($accessRequest->password_plain ?: 'password123'),
                ]
            );

            StaffProfile::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'role_id'       => $accessRequest->role_id,
                    'employee_code' => sprintf('MALA-%03d', $u->id),
                    'phone'         => $accessRequest->phone,
                    'is_active'     => true,
                ]
            );

            $accessRequest->update([
                'status'      => 'APPROVED',
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);

            return $u;
        });

        return response()->json([
            'success' => true,
            'message' => "อนุมัติคำขอของ {$user->name} เรียบร้อยแล้ว",
        ]);
    }

    public function rejectAccessRequest(Request $request, AccessRequest $accessRequest)
    {
        $accessRequest->update([
            'status'      => 'REJECTED',
            'reviewed_by' => Auth::id(),
            'reviewed_at' => now(),
            'notes'       => $request->input('notes'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'ปฏิเสธคำขอสมัครใช้งานเรียบร้อยแล้ว',
        ]);
    }

    /**
     * ดึงสถิติจำนวนรายการสด + ประวัติถังสำรอง 30 วัน สำหรับหน้า "ระบบรีเซ็ทข้อมูล"
     */
    public function getResetStats(Request $request)
    {
        $branchIdParam  = $request->query('branch_id');
        $branchIdsParam = $request->query('branch_ids');

        $branchId = ($branchIdParam && $branchIdParam !== 'ALL') ? (int) $branchIdParam : null;
        $branchIds = null;
        if (is_array($branchIdsParam)) {
            $branchIds = $branchIdsParam;
        } elseif (is_string($branchIdsParam) && trim($branchIdsParam) !== '' && strtoupper(trim($branchIdsParam)) !== 'ALL') {
            $branchIds = explode(',', $branchIdsParam);
        }

        $targetBranchIds = DataResetService::normalizeBranchIds($branchId, $branchIds);
        $stats = DataResetService::getLiveStats($branchId, $targetBranchIds);

        $snapshots = collect();
        if (Schema::hasTable('data_reset_snapshots')) {
            $snapshotsQuery = DataResetSnapshot::with('branch')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->latest('id')
                ->take(50);

            if (!empty($targetBranchIds)) {
                $snapshotsQuery->where(function ($q) use ($targetBranchIds) {
                    $q->whereIn('branch_id', $targetBranchIds)->orWhereNull('branch_id');
                });
            }

            $snapshots = $snapshotsQuery->get()->map(function (DataResetSnapshot $s) {
                $daysLeft = $s->expires_at ? max(0, (int) now()->diffInDays($s->expires_at, false)) : 30;
                return [
                    'id'             => $s->id,
                    'batch_number'   => $s->batch_number,
                    'scope_key'      => $s->scope_key,
                    'scope_label'    => $s->scope_label,
                    'branch_name'    => $s->branch?->name ?? 'ทุกสาขา / หลายสาขา',
                    'date_filter'    => $s->date_filter,
                    'records_count'  => $s->records_count,
                    'total_amount'   => (float) $s->total_amount,
                    'summary_notes'  => $s->summary_notes,
                    'performer_name' => $s->performer_name ?? 'Admin',
                    'status'         => $s->status,
                    'restored_at'    => $s->restored_at ? $s->restored_at->format('d/m/Y H:i') : null,
                    'restorer_name'  => $s->restorer_name,
                    'created_at_fmt' => $s->created_at ? $s->created_at->format('d/m/Y H:i') : '-',
                    'days_left'      => $daysLeft,
                ];
            });
        }

        return response()->json([
            'success'   => true,
            'stats'     => $stats,
            'snapshots' => $snapshots,
        ]);
    }

    /**
     * ส่งออกข้อมูลลูกค้าสมาชิกเป็นไฟล์ .CSV เพื่อสำรองเก็บไว้ในเครื่องก่อนกดรีเซ็ท
     */
    public function exportCustomersBackupCsv(Request $request)
    {
        $table = Schema::hasTable('customer_rewards') ? 'customer_rewards' : (Schema::hasTable('customers') ? 'customers' : null);
        if (!$table) {
            abort(404, 'ไม่พบตารางสมาชิก');
        }

        $query = DB::table($table);
        if (Schema::hasColumn($table, 'deleted_at')) {
            $query->whereNull('deleted_at');
        }
        $rows = $query->orderBy('id')->get();

        $filename = 'mala_street_customers_backup_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            if ($rows->isEmpty()) {
                fputcsv($out, ['id', 'customer_name', 'customer_phone', 'points', 'total_stamps', 'free_skewers_redeemed', 'created_at']);
            } else {
                $first = (array) $rows->first();
                fputcsv($out, array_keys($first));
                foreach ($rows as $r) {
                    fputcsv($out, array_values((array) $r));
                }
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * สั่งรีเซ็ทข้อมูลตามหมวดที่เลือก พร้อมเก็บลงถังสำรอง 30 วัน
     */
    public function executeDataReset(Request $request)
    {
        $actor = Auth::user();
        if ($actor->getRoleName() !== 'admin' && !AccessControlService::userHasPermission($actor, 'access.reset.delete')) {
            return response()->json(['success' => false, 'message' => 'เฉพาะเจ้าของร้าน (Super Admin) เท่านั้นที่สามารถรีเซ็ทข้อมูลได้'], 403);
        }

        $validated = $request->validate([
            'scope_key'     => ['required', 'string'],
            'date_filter'   => ['nullable', 'string'],
            'branch_id'     => ['nullable'],
            'branch_ids'    => ['nullable', 'array'],
            'confirm_mode'  => ['required', 'in:code,password'],
            'confirm_value' => ['required', 'string'],
            'reason'        => ['nullable', 'string', 'max:500'],
        ]);

        if (!DataResetService::verifyConfirmation($actor, $validated['confirm_mode'], $validated['confirm_value'])) {
            $errMsg = $validated['confirm_mode'] === 'password'
                ? 'รหัสผ่านแอดมินไม่ถูกต้อง กรุณาลองอีกครั้ง'
                : 'กรุณาพิมพ์คำว่า RESET ให้ถูกต้อง';
            return response()->json(['success' => false, 'message' => $errMsg], 422);
        }

        $branchId = (!empty($validated['branch_id']) && $validated['branch_id'] !== 'ALL')
            ? (int) $validated['branch_id']
            : null;
        $targetBranchIds = DataResetService::normalizeBranchIds($branchId, $validated['branch_ids'] ?? null);

        $snapshot = DataResetService::resetScope(
            scopeKey: $validated['scope_key'],
            dateFilter: $validated['date_filter'] ?? 'ALL',
            branchId: $branchId,
            actor: $actor,
            reason: $validated['reason'] ?? null,
            branchIds: $targetBranchIds
        );

        return response()->json([
            'success'  => true,
            'message'  => "รีเซ็ท [{$snapshot->scope_label}] สำเร็จ ({$snapshot->records_count} รายการ) และสำรองข้อมูลไว้ 30 วันเรียบร้อยแล้ว",
            'snapshot' => $snapshot,
        ]);
    }

    /**
     * กู้คืนข้อมูลรายชุดจากถังสำรอง 30 วัน
     */
    public function restoreDataResetSnapshot(Request $request, int $snapshot)
    {
        $actor = Auth::user();
        $validated = $request->validate([
            'confirm_mode'  => ['required', 'in:code,password'],
            'confirm_value' => ['required', 'string'],
        ]);

        if (!DataResetService::verifyConfirmation($actor, $validated['confirm_mode'], $validated['confirm_value'])) {
            return response()->json(['success' => false, 'message' => 'การยืนยันตัวตนไม่ถูกต้อง'], 422);
        }

        $restored = DataResetService::restoreSnapshot($snapshot, $actor);

        return response()->json([
            'success'  => true,
            'message'  => "กู้คืนข้อมูล [{$restored->scope_label}] จำนวน {$restored->records_count} รายการ กลับเข้าสู่ระบบเรียบร้อยแล้ว",
            'snapshot' => $restored,
        ]);
    }

    /**
     * กู้คืนข้อมูลทั้งหมดที่อยู่ในถังสำรอง 30 วันในคลิกเดียว
     */
    public function restoreAllDataResetSnapshots(Request $request)
    {
        $actor = Auth::user();
        $validated = $request->validate([
            'confirm_mode'  => ['required', 'in:code,password'],
            'confirm_value' => ['required', 'string'],
        ]);

        if (!DataResetService::verifyConfirmation($actor, $validated['confirm_mode'], $validated['confirm_value'])) {
            return response()->json(['success' => false, 'message' => 'การยืนยันตัวตนไม่ถูกต้อง'], 422);
        }

        $res = DataResetService::restoreAllActiveSnapshots($actor);

        return response()->json([
            'success' => true,
            'message' => "กู้คืนข้อมูลทั้งหมด {$res['snapshots_restored']} ชุด (รวม {$res['records_restored']} รายการ) เรียบร้อยแล้ว",
            'result'  => $res,
        ]);
    }

    /**
     * ลบชุดข้อมูลสำรองออกจากถัง 30 วันอย่างถาวร (ไม่กระทบข้อมูลจริงหน้าร้าน)
     */
    public function purgeDataResetSnapshot(Request $request, int $snapshot)
    {
        $actor = Auth::user();
        $validated = $request->validate([
            'confirm_mode'  => ['required', 'in:code,password'],
            'confirm_value' => ['required', 'string'],
        ]);

        if (!DataResetService::verifyConfirmation($actor, $validated['confirm_mode'], $validated['confirm_value'])) {
            return response()->json(['success' => false, 'message' => 'การยืนยันตัวตนไม่ถูกต้อง'], 422);
        }

        $purged = DataResetService::purgeSnapshot($snapshot, $actor);

        return response()->json([
            'success' => true,
            'message' => "ลบชุดสำรอง [{$purged['batch_number']} - {$purged['scope_label']}] ออกจากถังสำรองถาวรเรียบร้อยแล้ว",
        ]);
    }
}
