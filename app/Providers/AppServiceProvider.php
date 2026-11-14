<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\MasterSummary;
use App\Models\Setting;
use App\Models\User;
use App\Services\AccessControlService;
use App\Support\LoginLandingResolver;
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
            // Skip heavy shared DB queries when rendering the fast login screen
            if ($view->getName() === 'auth.login') {
                return;
            }

            // 3. Resolve current user profile (Synced across Auth, Session Switcher, and Access Control list)
            $user = Auth::user();
            $profile = null;
            $savedUsers = LoginLandingResolver::getSavedUsersList();

            $isLoggedOut = session('is_logged_out', false);
            $sessionProfile = session('auth_user_profile');

            if ($user && !$isLoggedOut) {
                $profile = LoginLandingResolver::resolveProfile($user, is_array($sessionProfile) ? $sessionProfile : null);

                if (!empty($profile['avatar']) && preg_match('#/(uploads/.+)#', $profile['avatar'], $avatarMatches)) {
                    $profile['avatar'] = '/' . ltrim($avatarMatches[1], '/');
                }
            }

            // 4. Resolve global auto-logout settings (cached once per request)
            static $cachedAutoLogoutSettings = null;
            if ($cachedAutoLogoutSettings === null) {
                $savedSettings = json_decode(Setting::get('access_auto_logout_settings', '{}'), true) ?: [];
                $cachedAutoLogoutSettings = array_merge([
                    'timeout_minutes' => (int) Setting::get('access_auto_logout_minutes', 15),
                    'timeout_action' => 'full_logout',
                    'exempt_kitchen' => true,
                    'exempt_customer' => true,
                    'lock_on_blur' => false,
                    'warning_countdown' => true,
                    'require_pin_shift_close' => true,
                    'audit_log' => true,
                ], $savedSettings);
            }

            // 5. Resolve current user effective permissions (cached per user in request)
            $currentUserPermissions = [];
            if ($user && !$isLoggedOut) {
                $currentUserPermissions = AccessControlService::getEffectivePermissions($user);
            }

            $view->with('currentUser', $profile);
            $view->with('currentUserPermissions', $currentUserPermissions);
            $view->with('allSystemUsers', $savedUsers);
            $view->with('globalAutoLogoutSettings', $cachedAutoLogoutSettings);
            $view->with('thaiServerNow', ThaiDate::now());
        });
    }
}
