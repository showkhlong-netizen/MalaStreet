<?php

namespace App\Providers;

use App\Models\Setting;
use App\Models\User;
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
        // Enforce HTTPS on Production / Cloud Hosting (Render) to fix mixed-content CSS blocking
        if (app()->environment('production') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') || str_contains(request()->url(), 'onrender.com') || env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }

        View::composer('*', function ($view) {
            // 1. Resolve current user profile
            $user = Auth::user();
            $profile = null;
            $savedUsers = json_decode(Setting::get('access_users_list', '[]'), true) ?: [];

            $isLoggedOut = session('is_logged_out', false);

            if ($user && !$isLoggedOut) {
                // Find matching profile in access_users_list
                $profile = collect($savedUsers)->firstWhere('email', $user->email);
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
            } elseif (!$isLoggedOut) {
                // If not explicitly logged out, default to primary active user
                if (!empty($savedUsers)) {
                    $profile = $savedUsers[0];
                } else {
                    $profile = [
                        'id' => 'usr_main_admin',
                        'name' => 'Munib Salaemae',
                        'full_name' => 'Munib Salaemae',
                        'email' => 'salaemaemunib@gmail.com',
                        'role' => 'Admin',
                        'role_label' => 'ผู้ดูแลระบบ (Admin)',
                        'initials' => 'Mu',
                        'color' => 'bg-rose-600',
                        'avatar' => asset('uploads/users/user_1794212895_aCeDLF.png'),
                        'status' => 'online',
                        'status_label' => 'ใช้งานอยู่',
                        'branch' => 'ทุกสาขา',
                    ];
                }

                // Automatically authenticate this default user
                $defaultUser = User::where('email', $profile['email'])->first();
                if ($defaultUser) {
                    Auth::login($defaultUser);
                }
            }

            // 2. Resolve global auto-logout settings
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
            $view->with('globalAutoLogoutSettings', $autoLogoutSettings);
        });
    }
}
