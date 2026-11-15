<?php

namespace Tests\Feature;

use App\Models\Setting;
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

    public function test_pos_hero_banner_survives_ephemeral_disk_wipe_after_deploy(): void
    {
        $admin = User::create([
            'name' => 'Admin POS Banner Persist',
            'email' => 'admin_pos_persist@malastreet.local',
            'password' => Hash::make('password123'),
        ]);

        // Tiny valid 1x1 PNG base64 (> 50 bytes)
        $sampleBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $uploadRes = $this->actingAs($admin)->postJson(route('online.hero_banner.upload'), [
            'scope' => 'pos',
            'image_base64' => $sampleBase64,
        ]);

        $uploadRes->assertStatus(200)->assertJson(['success' => true]);
        $uploadedUrl = $uploadRes->json('url');
        $this->assertNotEmpty($uploadedUrl);

        // Delete physical file to simulate Render container redeploy / ephemeral disk wipe
        $parsedPath = parse_url($uploadedUrl, PHP_URL_PATH);
        $diskPath = public_path(ltrim($parsedPath, '/'));
        if (file_exists($diskPath)) {
            @unlink($diskPath);
        }
        $this->assertFileDoesNotExist($diskPath);

        // Reload POS page -> should restore file from DB backup and render the image
        $posRes = $this->actingAs($admin)->get(route('pos.index'));
        $posRes->assertStatus(200);
        $this->assertStringContainsString($uploadedUrl, $posRes->getContent());
        $this->assertFileExists($diskPath);

        // Clean up test upload file
        @unlink($diskPath);
    }
}
