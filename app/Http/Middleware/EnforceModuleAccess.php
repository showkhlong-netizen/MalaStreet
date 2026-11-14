<?php

namespace App\Http\Middleware;

use App\Services\AccessControlService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware ตรวจสอบสถานะพนักงานและสิทธิ์การเข้าถึงทุกโมดูลของเว็บหม่าล่าสตรีท
 */
class EnforceModuleAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();
        if (!$user) {
            return $next($request);
        }

        // 1. ตรวจสอบว่าบัญชีพนักงานถูกระงับการใช้งานหรือไม่
        $profile = Schema::hasTable('staff_profiles') ? $user->staffProfile : null;
        if ($profile && !$profile->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success'       => false,
                    'access_denied' => true,
                    'message'       => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อเจ้าของร้าน',
                ], 403);
            }

            return redirect()->route('login')->withErrors([
                'email' => 'บัญชีของคุณถูกระงับการใช้งาน กรุณาติดต่อเจ้าของร้าน',
            ]);
        }

        // 2. ตรวจสอบสิทธิ์ตามชื่อ Route ปัจจุบัน
        $routeName = $request->route()?->getName();
        $requirement = AccessControlService::getRoutePermissionRequirement($routeName);

        if (!$requirement) {
            return $next($request);
        }

        $requiredKey = $requirement['key'];
        $altKey = $requirement['alt_key'] ?? null;
        $moduleLabel = $requirement['label'] ?? 'ส่วนนี้';

        $hasAccess = AccessControlService::userHasPermission($user, $requiredKey)
            || ($altKey && AccessControlService::userHasPermission($user, $altKey));

        if ($hasAccess) {
            return $next($request);
        }

        $deniedMessage = ($user->name ?? 'พนักงาน') . " ไม่มีสิทธิ์เข้าถึงหน้า [{$moduleLabel}]";

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'        => false,
                'access_denied'  => true,
                'user_name'      => $user->name ?? 'พนักงาน',
                'module_label'   => $moduleLabel,
                'permission_key' => $requiredKey,
                'message'        => $deniedMessage,
            ], 403);
        }

        $previousUrl = url()->previous();
        $currentUrl = $request->fullUrl();
        $fallbackUrl = AccessControlService::getFirstAccessibleRoute($user);

        $redirectTo = ($previousUrl && $previousUrl !== $currentUrl && !str_contains($previousUrl, '/login'))
            ? $previousUrl
            : $fallbackUrl;

        return redirect()->to($redirectTo)->with('access_denied_popup', [
            'title'   => 'ไม่มีสิทธิ์เข้าถึงส่วนนี้',
            'user'    => $user->name ?? 'พนักงาน',
            'module'  => $moduleLabel,
            'message' => $deniedMessage,
        ]);
    }
}
