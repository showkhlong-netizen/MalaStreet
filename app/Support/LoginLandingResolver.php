<?php

namespace App\Support;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;

class LoginLandingResolver
{
    /**
     * In-memory per-request cache for access_users_list to avoid repeated DB queries.
     */
    protected static ?array $cachedUsersList = null;

    public static function getSavedUsersList(bool $fresh = false): array
    {
        if ($fresh || static::$cachedUsersList === null) {
            static::$cachedUsersList = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];
        }

        return static::$cachedUsersList;
    }

    /**
     * Resolve the authoritative profile from Access Control list or fallback User model.
     */
    public static function resolveProfile(?User $user, ?array $sessionProfile = null): ?array
    {
        if (!$user) {
            return null;
        }

        $savedUsers = static::getSavedUsersList();
        $userEmail = strtolower(trim((string) $user->email));

        // 1. Match by authenticated user's email first (case-insensitive)
        $matched = collect($savedUsers)->first(function ($u) use ($userEmail) {
            return strtolower(trim((string) ($u['email'] ?? ''))) === $userEmail;
        });

        // 2. If sessionProfile was switched via switchUser inside the app, validate against savedUsers
        if ($sessionProfile && is_array($sessionProfile) && !empty($sessionProfile['id'])) {
            $switchedMatch = collect($savedUsers)->firstWhere('id', $sessionProfile['id']);
            if ($switchedMatch) {
                $matched = $switchedMatch;
            }
        }

        if ($matched && is_array($matched)) {
            return $matched;
        }

        // 3. Strict fallback when user is not in access_users_list
        $isRootAdmin = in_array($userEmail, ['salaemaemunib@gmail.com', 'admin@malastreet.com'], true)
            || ((int) $user->id === 1);

        return [
            'id' => 'usr_' . $user->id,
            'name' => $user->name,
            'full_name' => $user->name,
            'email' => $user->email,
            'role' => $isRootAdmin ? 'Admin' : 'Employee',
            'role_label' => $isRootAdmin ? 'ผู้ดูแลระบบ (Admin)' : 'พนักงานร้าน (Employee)',
            'initials' => mb_substr($user->name, 0, 2),
            'color' => $isRootAdmin ? 'bg-rose-600' : 'bg-blue-600',
            'avatar' => null,
            'status' => 'online',
            'status_label' => 'ใช้งานอยู่',
            'branch' => $isRootAdmin ? 'ทุกสาขา' : 'สาขายะลา',
        ];
    }

    /**
     * Strictly determine whether a user/profile is an Admin.
     */
    public static function isAdmin(?User $user, ?array $profile = null): bool
    {
        if (!$user) {
            return false;
        }

        $profile = $profile ?: static::resolveProfile($user, session('auth_user_profile'));
        $role = strtolower(trim((string) ($profile['role'] ?? '')));
        $roleLabel = strtolower(trim((string) ($profile['role_label'] ?? '')));

        if ($role === 'admin') {
            return true;
        }

        if (in_array($role, ['employee', 'staff', 'customer'], true)) {
            return false;
        }

        return str_contains($roleLabel, 'admin') || str_contains($roleLabel, 'ผู้ดูแลระบบ');
    }

    /**
     * Deterministic per-account identifier key for daily first-login tracking.
     */
    public static function accountKey(User $user, ?array $profile = null): string
    {
        $profile = $profile ?: static::resolveProfile($user, session('auth_user_profile'));
        $email = strtolower(trim((string) ($profile['email'] ?? $user->email)));
        $id = (string) ($profile['id'] ?? ('usr_' . $user->id));

        return md5($id . '|' . $email);
    }

    /**
     * Current authoritative date string in Thailand timezone (Asia/Bangkok).
     */
    public static function todayThaiDate(): string
    {
        return Carbon::now('Asia/Bangkok')->format('Y-m-d');
    }

    /**
     * Check whether an Employee needs their first-login-of-the-day redirect to /admin/employees.
     */
    public static function needsEmployeeDailyFirstLoginRedirect(User $user, ?array $profile = null, bool $useSessionCache = true): bool
    {
        if (static::isAdmin($user, $profile)) {
            return false;
        }

        $today = static::todayThaiDate();
        $acctKey = static::accountKey($user, $profile);
        $sessionKey = 'emp_first_login_date_' . $acctKey;

        if ($useSessionCache && session($sessionKey) === $today) {
            return false;
        }

        $settingKey = 'emp_first_login_' . $acctKey;
        $lastLoginDate = (string) Setting::get($settingKey, '');

        if ($lastLoginDate === $today) {
            session([$sessionKey => $today]);
            return false;
        }

        return true;
    }

    /**
     * Mark that this Employee account has completed its first-login-of-day redirect today.
     */
    public static function markEmployeeDailyFirstLoginDone(User $user, ?array $profile = null): void
    {
        $today = static::todayThaiDate();
        $acctKey = static::accountKey($user, $profile);

        Setting::set('emp_first_login_' . $acctKey, $today, 'auth_daily');
        session(['emp_first_login_date_' . $acctKey => $today]);
    }

    /**
     * Determine the authoritative landing URL for an authenticated user:
     * - Admin: Store Overview (/admin -> route('admin.dashboard')) by default.
     * - Employee (first login of account today): Face-Scan Clock-In (/admin/employees).
     * - Employee (subsequent login same day): POS Cashier (/pos).
     */
    public static function resolveLandingUrl(
        User $user,
        ?array $profile = null,
        ?string $explicitRedirect = null,
        bool $markEmployeeOnFirstRedirect = true
    ): string {
        $profile = $profile ?: static::resolveProfile($user, session('auth_user_profile'));

        if (static::isAdmin($user, $profile)) {
            if ($explicitRedirect && static::isSafeCustomRedirect($explicitRedirect)) {
                return $explicitRedirect;
            }
            return route('admin.dashboard');
        }

        if (static::needsEmployeeDailyFirstLoginRedirect($user, $profile, false)) {
            if ($markEmployeeOnFirstRedirect) {
                static::markEmployeeDailyFirstLoginDone($user, $profile);
            }
            return route('admin.employees.index');
        }

        if ($explicitRedirect && static::isSafeCustomRedirect($explicitRedirect)) {
            return $explicitRedirect;
        }

        return route('pos.index');
    }

    /**
     * Validate that an explicit redirect path is a safe internal non-root, non-login path.
     */
    protected static function isSafeCustomRedirect(string $url): bool
    {
        $parsed = parse_url($url);
        if (!$parsed) {
            return false;
        }

        if (!empty($parsed['host']) && $parsed['host'] !== request()->getHost()) {
            return false;
        }

        $path = '/' . ltrim((string) ($parsed['path'] ?? '/'), '/');
        if ($path === '/' || str_starts_with($path, '/login') || str_starts_with($path, '/logout')) {
            return false;
        }

        return true;
    }
}
