<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\MasterSummary;
use App\Models\Setting;
use App\Models\User;
use App\Support\ThaiDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Strictly enforce Thailand Timezone (Asia/Bangkok) & Thai Locale across the entire application
        date_default_timezone_set('Asia/Bangkok');
        Carbon::setLocale('th');

        Carbon::macro('toThaiDate', function (bool $includeTime = false, bool $fullMonth = false): string {
            /** @var Carbon $this */
            return ThaiDate::format($this, $includeTime, $fullMonth);
        });

        Carbon::macro('toThaiSlash', function (bool $includeTime = false): string {
            /** @var Carbon $this */
            return ThaiDate::formatSlash($this, $includeTime);
        });

        // 2. Enforce HTTPS on Production / Cloud Hosting (Render) to fix mixed-content CSS blocking
        if (app()->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || str_contains(request()->url(), 'onrender.com') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            // 3. Resolve current user profile (Synced across Auth, Session Switcher, and Access Control list)
            $user = Auth::user();
            $profile = null;
            $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];

            $isLoggedOut = session('is_logged_out', false);
            $sessionProfile = session('auth_user_profile');

            if ($user && !$isLoggedOut) {
                if ($sessionProfile && is_array($sessionProfile) && !empty($sessionProfile['id'])) {
                    // Refresh from latest access_users_list if available
                    $freshMatch = collect($savedUsers)->firstWhere('id', $sessionProfile['id'])
                        ?? collect($savedUsers)->firstWhere('email', $user->email);
                    $profile = $freshMatch ?: $sessionProfile;
                } else {
                    $profile = collect($savedUsers)->firstWhere('email', $user->email);
                }

                if (!$profile) {
                    $profile = [
                        'id' => 'usr_' . $user->id,
                        'name' => $user->name,
                        'full_name' => $user->name,
                        'email' => $user->email,
                        'role' => 'Admin',
                        'role_label' => 'ผู้ดูแลระบบ (Admin)',
                        'initials' => mb_substr($user->name, 0, 2),
                        'color' => 'bg-rose-600',
                        'avatar' => null,
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'ทุกสาขา',
                    ];
                }

                if (!empty($profile['avatar']) && preg_match('#/(uploads/.+)#', $profile['avatar'], $avatarMatches)) {
                    $profile['avatar'] = '/' . ltrim($avatarMatches[1], '/');
                }
            }

            // 4. Resolve global auto-logout settings
            $savedSettings = json_decode(Setting::get('access_auto_logout_settings', '{}'), true) ?: [];
            $autoLogoutSettings = array_merge([
                'timeout_minutes' => (int) Setting::get('access_auto_logout_minutes', 15),
                'timeout_action' => 'full_logout',
                'exempt_kitchen' => true,
                'exempt_customer' => true,
                'lock_on_blur' => false,
                'warning_countdown' => true,
                'require_pin_shift_close' => true,
                'audit_log' => true,
            ], $savedSettings);

            $view->with('currentUser', $profile);
            $view->with('allSystemUsers', $savedUsers);
            $view->with('globalAutoLogoutSettings', $autoLogoutSettings);
            $view->with('thaiServerNow', ThaiDate::now());
        });
    }
}
