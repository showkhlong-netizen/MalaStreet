<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EmployeePasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_set_short_four_char_employee_password_persist_across_revisit_and_login(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'salaemaemunib@gmail.com',
            'password' => Hash::make('password123'),
        ]);

        // Ensure test accounts cleared state with no User row yet for Arena
        Setting::set('access_test_accounts_cleared', '1', 'access');

        // 1. Save simple 4-character password/PIN for employee
        $saveResponse = $this->actingAs($admin)->postJson('/admin/access/employee/save', [
            'user_id' => 'usr_arena_employee',
            'profile' => [
                'name' => 'อารีนา เจ๊ะอามะ',
                'position' => 'พนง. บริการประจำ',
                'phone' => '082-345-6789',
                'email' => 'arena@malastreet.com',
                'branch' => 'สาขายะลา',
                'role' => 'Employee',
                'password' => '1234',
            ],
        ]);

        $saveResponse->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.profile.password', '1234');

        // 2. Revisiting the page still shows the saved password value in #emp-edit-password
        $pageResponse = $this->actingAs($admin)->get('/admin/access?tab=permissions&user_id=usr_arena_employee');
        $pageResponse->assertOk();
        $pageResponse->assertSee('value="1234"', false);
        $pageResponse->assertSee('บันทึกรหัสผ่านแล้ว', false);

        // 3. Subsequent save on another tab with blank password preserves '1234'
        $reSaveResponse = $this->actingAs($admin)->postJson('/admin/access/employee/save', [
            'user_id' => 'usr_arena_employee',
            'profile' => [
                'name' => 'อารีนา เจ๊ะอามะ',
                'email' => 'arena@malastreet.com',
                'password' => '',
            ],
        ]);
        $reSaveResponse->assertOk()
            ->assertJsonPath('data.profile.password', '1234');

        // 4. Logout admin and verify employee can log in with 4-char password '1234'
        $this->post('/logout');

        $badLogin = $this->post('/login', [
            'email' => 'arena@malastreet.com',
            'password' => 'password123',
        ]);
        $badLogin->assertRedirect();
        $this->assertGuest();

        $goodLogin = $this->post('/login', [
            'email' => 'arena@malastreet.com',
            'password' => '1234',
        ]);
        $goodLogin->assertRedirect();
        $this->assertAuthenticated();
        $this->assertSame('arena@malastreet.com', auth()->user()->email);
    }
}
