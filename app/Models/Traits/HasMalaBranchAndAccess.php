<?php

namespace App\Models\Traits;

use App\Models\Branch;
use App\Models\Setting;
use App\Models\StaffProfile;
use App\Services\AccessControlService;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Trait สำหรับใส่ใน App\Models\User ของเว็บหม่าล่าสตรีท
 * วิธีใช้: เพียงเพิ่ม `use \App\Models\Traits\HasMalaBranchAndAccess;` ในคลาส User
 */
trait HasMalaBranchAndAccess
{
    public function staffProfile(): HasOne
    {
        return $this->hasOne(StaffProfile::class, 'user_id');
    }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'branch_user', 'user_id', 'branch_id')->withTimestamps();
    }

    public function getRoleName(): string
    {
        if (Schema::hasTable('staff_profiles') && Schema::hasTable('roles')) {
            $profile = $this->staffProfile;
            if ($profile && $profile->role_id) {
                $role = DB::table('roles')->where('id', $profile->role_id)->first();
                if ($role) {
                    return $role->name;
                }
            }
        }

        // Sync with MalaStreet's Setting('access_users_list') role if present
        try {
            if (Schema::hasTable('settings')) {
                $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
                $email = strtolower(trim((string) $this->email));
                foreach ($savedUsers as $u) {
                    if (strtolower(trim((string) ($u['email'] ?? ''))) === $email) {
                        $rawRole = strtolower((string) ($u['role'] ?? ''));
                        if (str_contains($rawRole, 'admin')) {
                            return 'admin';
                        }
                        if (str_contains($rawRole, 'manager')) {
                            return 'manager';
                        }
                        if (str_contains($rawRole, 'kitchen')) {
                            return 'kitchen';
                        }
                        if (str_contains($rawRole, 'stock')) {
                            return 'stock';
                        }
                        if (str_contains($rawRole, 'finance') || str_contains($rawRole, 'account')) {
                            return 'finance';
                        }
                        return 'cashier';
                    }
                }
            }
        } catch (\Throwable $e) {
            // Fallback below
        }

        return ((int) $this->id === 1 || in_array(strtolower((string) $this->email), ['admin@malastreet.com', 'salaemaemunib@gmail.com'], true))
            ? 'admin'
            : 'cashier';
    }

    public function getRoleLabel(): string
    {
        if (Schema::hasTable('staff_profiles') && Schema::hasTable('roles')) {
            $profile = $this->staffProfile;
            if ($profile && $profile->role_id) {
                $role = DB::table('roles')->where('id', $profile->role_id)->first();
                if ($role) {
                    return $role->label;
                }
            }
        }

        return $this->getRoleName() === 'admin' ? 'เจ้าของร้าน (Super Admin)' : 'แคชเชียร์ / พนักงานร้าน';
    }

    public function hasPermission(string $permissionKey): bool
    {
        return AccessControlService::userHasPermission($this, $permissionKey);
    }

    public function getEffectivePermissions(): array
    {
        return AccessControlService::getEffectivePermissions($this);
    }

    /**
     * ดึงรายการสาขาที่ผู้ใช้งานได้รับอนุญาตให้เข้าถึง
     */
    public function getAllowedBranches()
    {
        if (!Schema::hasTable('branches')) {
            return collect();
        }

        if (Schema::hasTable('branch_user')) {
            $assigned = $this->branches()->where('status', 'ACTIVE')->orderBy('is_main', 'desc')->orderBy('id')->get();
            if ($assigned->isNotEmpty()) {
                return $assigned;
            }
        }

        // กรณีเจ้าของร้าน (admin) ที่ไม่ได้ผูกสาขาเฉพาะ ให้เข้าถึงได้ทุกสาขาที่เปิดใช้งาน
        if ($this->getRoleName() === 'admin') {
            return Branch::where('status', 'ACTIVE')->orderBy('is_main', 'desc')->orderBy('id')->get();
        }

        $main = Branch::where('status', 'ACTIVE')->orderBy('is_main', 'desc')->orderBy('id')->first();
        return $main ? collect([$main]) : collect();
    }

    /**
     * ดึงสาขาที่กำลังทำงานอยู่ในปัจจุบัน (Active Branch)
     */
    public function getActiveBranch(): ?Branch
    {
        if (!Schema::hasTable('branches')) {
            return null;
        }

        $allowed = $this->getAllowedBranches();
        if ($allowed->isEmpty()) {
            return Branch::orderBy('is_main', 'desc')->orderBy('id')->first();
        }

        if ($allowed->count() === 1 && $this->getRoleName() !== 'admin') {
            return $allowed->first();
        }

        $sessionBranchId = session('active_branch_id');
        if ($sessionBranchId) {
            $matched = $allowed->firstWhere('id', (int) $sessionBranchId);
            if ($matched) {
                return $matched;
            }
            if ($this->getRoleName() === 'admin') {
                $anyBranch = Branch::where('status', 'ACTIVE')->find((int) $sessionBranchId);
                if ($anyBranch) {
                    return $anyBranch;
                }
            }
        }

        return $allowed->first();
    }

    public function getActiveBranchId(): int
    {
        $branch = $this->getActiveBranch();
        return $branch ? (int) $branch->id : 1;
    }

    /**
     * ตรวจสอบว่าผู้ใช้งานสามารถสลับสาขาบน Topbar ได้หรือไม่
     */
    public function canSwitchBranch(): bool
    {
        if (!Schema::hasTable('branches')) {
            return false;
        }
        if ($this->getRoleName() === 'admin') {
            return Branch::where('status', 'ACTIVE')->count() > 1;
        }
        return $this->getAllowedBranches()->count() > 1;
    }
}
