<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeeRealtimeSyncTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_payroll_and_attendance_sync_with_access_control_and_exclude_mock_data(): void
    {
        // Freeze time to Saturday 2026-11-14 10:00:00 Asia/Bangkok
        Carbon::setTestNow(Carbon::create(2026, 11, 14, 10, 0, 0, 'Asia/Bangkok'));

        $admin = User::create([
            'name' => 'Munib Admin',
            'email' => 'admin@malastreet.com',
            'password' => Hash::make('1122'),
        ]);

        // Seed 1 test account (must be excluded) + 1 real daily employee + 1 real monthly employee
        Setting::set('access_users_list', json_encode([
            [
                'id' => 'usr_mock_test',
                'name' => 'Mock User',
                'email' => 'mock@example.com',
                'role' => 'Employee',
                'role_label' => 'พนักงาน',
                'branch' => 'สาขายะลา',
                'is_test' => true,
            ],
            [
                'id' => 'usr_salsabil',
                'name' => 'salsabil sahoh',
                'email' => 'salsabil@malastreet.com',
                'role' => 'Employee',
                'role_label' => 'พนง. บริการประจำ',
                'branch' => 'สาขายะลา',
                'is_test' => false,
                'created_at' => '2026-10-01 09:00:00',
            ],
            [
                'id' => 'usr_munib',
                'name' => 'Munib Salaemae',
                'email' => 'admin@malastreet.com',
                'role' => 'Admin',
                'role_label' => 'ผู้ดูแลระบบ (Admin)',
                'branch' => 'ทุกสาขา',
                'is_test' => false,
                'created_at' => '2026-10-01 09:00:00',
            ],
        ]), 'access');

        // Configure salsabil in Access Control: Mon-Fri, 09:00-19:00, daily_wage 300, grace 1x/month 15m, late_fine 50
        Setting::set('employee_data_usr_salsabil', json_encode([
            'schedule' => [
                'days' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์'],
                'start_time' => '09:00 AM',
                'end_time' => '07:00 PM',
            ],
            'profile' => [
                'name' => 'salsabil sahoh',
                'position' => 'พนง. บริการประจำ',
                'branch' => 'สาขายะลา',
                'role' => 'Employee',
                'email' => 'salsabil@malastreet.com',
            ],
            'payroll' => [
                'base_salary' => 15000, // legacy default that must NOT override daily wage_kpi
                'allowance' => 1000,
                'deduction' => 750,
            ],
            'leave' => [
                'late_grace_times_month' => 1,
                'late_grace_minutes' => 15,
                'late_fine_amount' => 50,
            ],
            'more_info' => [
                'start_date' => '2025-01-01',
            ],
            'wage_kpi' => [
                'wage_type' => 'daily',
                'daily_wage' => 300,
                'monthly_wage' => 6429,
                'regular_ot_rate' => 50,
                'kpi_enabled' => true,
                'kpi_amount' => 500,
            ],
        ]), 'employees');

        // Record 3 attendance scans in Nov 2026 for salsabil (Mon-Fri elapsed in Nov 1..13 = 10 days -> 3 worked, 7 absent!)
        // Nov 2 (Mon): on time 08:55 -> 17:55 (9h)
        // Nov 3 (Tue): late 09:20 (past 09:15) -> 19:20 (10h20m, no OT wait 19:20 vs 19:00 = 20m OT)
        // Nov 4 (Wed): late 09:30 (past 09:15) -> 19:00 (9h30m)
        Setting::set('attendance_today_usr_salsabil_20261102', json_encode([
            'check_in' => '08:55 น.',
            'check_out' => '17:55 น.',
            'status' => 'ออกงานแล้ว',
        ]));
        Setting::set('attendance_today_usr_salsabil_20261103', json_encode([
            'check_in' => '09:20 น.',
            'check_out' => '19:00 น.',
            'status' => 'ออกงานแล้ว',
        ]));
        Setting::set('attendance_today_usr_salsabil_20261104', json_encode([
            'check_in' => '09:30 น.',
            'check_out' => '19:00 น.',
            'status' => 'ออกงานแล้ว',
        ]));

        // Also seed a legacy mock _20261108 sample stub that must be ignored
        Setting::set('attendance_today_usr_salsabil_20261108', json_encode([
            'check_in' => '09:00 น.',
            'check_in_photo' => '/uploads/attendance/sample_check_in.jpg',
            'status' => 'เข้างานแล้ว',
        ]));

        $res = $this->actingAs($admin)->getJson('/admin/employees/sync-data');
        $res->assertOk()->assertJsonPath('success', true);

        $cards = collect($res->json('payrollCards'));
        $this->assertNull($cards->firstWhere('id', 'usr_mock_test'));
        $this->assertCount(2, $cards);

        $salsabil = $cards->firstWhere('id', 'usr_salsabil');
        $this->assertNotNull($salsabil);
        $this->assertSame(3, $salsabil['work_days']);
        $this->assertSame(2, $salsabil['late_days']);
        $this->assertSame(7, $salsabil['absent_days']);
        $this->assertSame('3 / 10 วัน', $salsabil['attendance_ratio']);
        $this->assertSame('30%', $salsabil['rate_percent']);
        $this->assertSame('สาย 2 วัน', $salsabil['late_text']);
        $this->assertSame('ขาด 7 วัน', $salsabil['absent_text']);

        // Pay: 3 worked days * 300 = 900 base + 500 KPI - 1 billable late * 50 fine = 1350 net
        $this->assertEquals(900, $salsabil['base_salary']);
        $this->assertEquals(500, $salsabil['bonus']);
        $this->assertEquals(50, $salsabil['deduction_late']);
        $this->assertEquals(0, $salsabil['deduction_ss']);
        $this->assertEquals(1350, $salsabil['net_salary']);

        // Unconfigured Admin has clean 0 pay, real elapsed Mon-Fri absence = 10
        $munib = $cards->firstWhere('id', 'usr_munib');
        $this->assertSame(0, $munib['work_days']);
        $this->assertSame(10, $munib['absent_days']);
        $this->assertEquals(0, $munib['base_salary']);
        $this->assertEquals(0, $munib['net_salary']);

        // Overview logs mark late scans properly
        $logs = collect($res->json('allAttendanceLogs'));
        $nov3 = $logs->first(fn($l) => $l['employee_id'] === 'usr_salsabil' && $l['date'] === '2026-11-03');
        $this->assertSame('late', $nov3['status']);

        // Auto-sync test: when a new account is created in future, sync-data immediately includes it
        $regRes = $this->postJson('/register-request', [
            'name' => 'วาสนา ใหม่ล่าสุด',
            'email' => 'wasana.new@malastreet.com',
            'password' => '2233',
            'position' => 'แคชเชียร์',
        ]);
        $regRes->assertOk();

        $afterRes = $this->actingAs($admin)->getJson('/admin/employees/sync-data');
        $afterCards = collect($afterRes->json('payrollCards'));
        $this->assertCount(3, $afterCards);
        $wasana = $afterCards->firstWhere('email', 'wasana.new@malastreet.com');
        $this->assertNotNull($wasana);
        $this->assertSame(0, $wasana['work_days']);
        $this->assertSame(0, $wasana['absent_days']); // Hired today 2026-11-14 -> 0 prior absent days
        $this->assertEquals(0, $wasana['net_salary']);

        // Rendered /admin/employees HTML contains real period & no hardcoded 2567 mock periods
        $page = $this->actingAs($admin)->get('/admin/employees?tab=payroll');
        $page->assertOk();
        $page->assertDontSee('พฤศจิกายน 2567', false);
        $page->assertSee('วาสนา ใหม่ล่าสุด', false);
        $page->assertSee('ขาด 7 วัน', false);
        $page->assertSee('สาย 2 วัน', false);
    }
}
