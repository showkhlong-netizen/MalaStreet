<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use App\Support\LoginLandingResolver;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceDailyEmployeeLanding
{
    /**
     * Ensure every employee account lands on /admin/employees on their first visit/login of each day,
     * while leaving all other pages unlocked afterwards.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check() || session('is_logged_out', false)) {
            return $next($request);
        }

        // Only intercept standard HTML GET page navigations
        if (!$request->isMethod('GET') || $request->expectsJson() || $request->ajax()) {
            return $next($request);
        }

        // Never intercept auth, system, online, or embedded iframe chat endpoints
        if ($request->routeIs('login', 'login.submit', 'logout', 'logout.get', 'switch_user', 'pos.kitchen.chat', 'kitchen.chat', 'system.*')
            || $request->is('order', 'order/*')) {
            return $next($request);
        }

        $user = Auth::user();
        $profile = LoginLandingResolver::resolveProfile($user, session('auth_user_profile'));

        if (!LoginLandingResolver::isAdmin($user, $profile)) {
            if (LoginLandingResolver::needsEmployeeDailyFirstLoginRedirect($user, $profile, true)) {
                LoginLandingResolver::markEmployeeDailyFirstLoginDone($user, $profile);

                AuditLog::record(
                    'auth',
                    'daily_first_login',
                    'เข้าสู่ระบบครั้งแรกของวัน (พาไปหน้าสแกนหน้าเข้างาน)',
                    $profile['name'] ?? $user->name,
                    ['email' => $user->email, 'date' => LoginLandingResolver::todayThaiDate()]
                );

                if (!$request->routeIs('admin.employees.index')) {
                    return redirect()->route('admin.employees.index')
                        ->with('info', 'เข้าสู่ระบบครั้งแรกของวัน: ระบบพามาหน้าสแกนหน้าเข้างานประจำวันเรียบร้อยแล้ว');
                }
            }
        }

        return $next($request);
    }
}
