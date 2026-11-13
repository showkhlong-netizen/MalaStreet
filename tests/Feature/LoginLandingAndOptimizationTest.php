<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Support\LoginLandingResolver;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginLandingAndOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $employeeOne;
    protected User $employeeTwo;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 11, 13, 9, 0, 0, 'Asia/Bangkok'));

        $this->adminUser = User::create([
            'name' => 'Munib Salaemae',
            'email' => 'salaemaemunib@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        $this->employeeOne = User::create([
            'name' => 'meemee',
            'email' => 'meemee@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        $this->employeeTwo = User::create([
            'name' => 'salsabil sahoh',
            'email' => 'salsabil@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        Setting::set('access_users_list', json_encode([
            [
                'id' => 'usr_admin_1',
                'name' => 'Munib Salaemae',
                'email' => 'salaemaemunib@gmail.com',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบ (Admin)',
                'branch' => 'ทุกสาขา',
            ],
            [
                'id' => 'usr_emp_1',
                'name' => 'meemee',
                'email' => 'meemee@malastreet.com',
                'role' => 'Employee',
                'role_label' => 'พนักงานร้าน (Employee)',
                'branch' => 'สาขายะลา',
            ],
            [
                'id' => 'usr_emp_2',
                'name' => 'salsabil sahoh',
                'email' => 'salsabil@malastreet.com',
                'role' => 'Employee',
                'role_label' => 'พนักงานร้าน (Employee)',
                'branch' => 'สาขายะลา',
            ],
        ]), 'access');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_login_page_is_lightweight_has_no_quick_user_box_and_keeps_structure(): void
    {
        $response = $this->get(route('login'));

        $response->assertStatus(200);
        $response->assertSee('เข้าสู่ระบบการทำงาน');
        $response->assertSee('salaemaemunib@gmail.com');
        $response->assertSee('password123');
        $response->assertSee('กดลงทะเบียน เพื่อสร้างอีเมลพนักงานบัญชีใหม่');
        $response->assertSee('max-width: 314px', false);

        // Must NOT load slow Tailwind CDN JS or Quick User box (Image 2)
        $response->assertDontSee('cdn.tailwindcss.com');
        $response->assertDontSee('เลือกบัญชีที่บันทึกไว้เข้าสู่ระบบทันที');
        $response->assertDontSee('fillQuickUser');
    }

    public function test_admin_always_lands_on_store_overview_dashboard_by_default(): void
    {
        $response = $this->post(route('login.submit'), [
            'email' => 'salaemaemunib@gmail.com',
            'password' => 'password123',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        // Opening root '/' or '/login' while logged in as Admin goes straight to Store Overview
        $this->get('/')->assertRedirect(route('admin.dashboard'));
        $this->get(route('login'))->assertRedirect(route('admin.dashboard'));
    }

    public function test_employee_first_login_of_each_account_and_each_day_goes_to_employees_page_without_locking_other_pages(): void
    {
        // 1. Employee One's 1st login on 2026-11-13 -> redirected to /admin/employees
        $res1 = $this->post(route('login.submit'), [
            'email' => 'meemee@malastreet.com',
            'password' => 'password123',
        ]);
        $res1->assertRedirect(route('admin.employees.index'));

        // Other pages are NOT locked afterwards
        $this->get(route('pos.index'))->assertStatus(200);

        // Logout and log back in same day -> goes to POS
        $this->post(route('logout'));
        $res1Again = $this->post(route('login.submit'), [
            'email' => 'meemee@malastreet.com',
            'password' => 'password123',
        ]);
        $res1Again->assertRedirect(route('pos.index'));

        // 2. Employee Two's 1st login on 2026-11-13 (different account, same day) -> ALSO goes to /admin/employees
        $this->post(route('logout'));
        $res2 = $this->post(route('login.submit'), [
            'email' => 'salsabil@malastreet.com',
            'password' => 'password123',
        ]);
        $res2->assertRedirect(route('admin.employees.index'));

        // 3. Next day (2026-11-14): even if Employee Two stayed logged in via Remember Me and opens /pos -> redirected to /admin/employees once for the new day
        Carbon::setTestNow(Carbon::create(2026, 11, 14, 8, 30, 0, 'Asia/Bangkok'));
        $this->get(route('pos.index'))->assertRedirect(route('admin.employees.index'));

        // Subsequent navigation on 2026-11-14 works normally
        $this->get(route('pos.index'))->assertStatus(200);
    }
}
