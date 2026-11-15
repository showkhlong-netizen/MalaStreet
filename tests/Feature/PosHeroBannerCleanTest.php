<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosHeroBannerCleanTest extends TestCase
{
    use RefreshDatabase;

    public function test_pos_banner_has_no_store_logo_name_or_white_card_and_has_online_switch(): void
    {
        $admin = User::create([
            'name' => 'Admin POS',
            'email' => 'admin_pos_test@malastreet.local',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->actingAs($admin)->get(route('pos.index'));
        $res->assertStatus(200);

        $html = $res->getContent();
        $this->assertStringContainsString('เปิดบริการออนไลน์', $html);
        $this->assertStringContainsString('id="pos-hero-banner-section"', $html);
        $this->assertStringContainsString('id="btn-pos-master-settings"', $html);

        // Extract specifically the #pos-hero-banner-section HTML block
        $matched = preg_match('/<section id="pos-hero-banner-section".*?<\/section>/s', $html, $m);
        $this->assertSame(1, $matched, 'Could not locate #pos-hero-banner-section in rendered POS HTML');
        $bannerHtml = $m[0];

        $this->assertStringNotContainsString('logo-card.png', $bannerHtml);
        $this->assertStringNotContainsString('ร้านแนะนำ', $bannerHtml);
        $this->assertStringNotContainsString('ร้านหม่าล่าสตรีท (Mala Street)', $bannerHtml);
        $this->assertStringNotContainsString('หม่าล่า • ปิ้งย่าง • ทานเล่น', $bannerHtml);
        $this->assertStringContainsString('ระบบตรวจจับราคาขึ้น-ลง', $bannerHtml);
    }

    public function test_pos_online_switch_endpoint_toggles_setting(): void
    {
        $admin = User::create([
            'name' => 'Admin POS Toggle',
            'email' => 'admin_pos_toggle@malastreet.local',
            'password' => Hash::make('password123'),
        ]);

        $res = $this->actingAs($admin)
            ->postJson(route('pos.toggle_online'), ['mode' => 'live']);

        $res->assertStatus(200)
            ->assertJson(['success' => true, 'mode' => 'live']);

        $resOff = $this->actingAs($admin)
            ->postJson(route('pos.toggle_online'), ['mode' => 'preview']);

        $resOff->assertStatus(200)
            ->assertJson(['success' => true, 'mode' => 'preview']);
    }
}
