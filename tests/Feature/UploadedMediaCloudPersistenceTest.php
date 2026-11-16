<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\UploadedMedia;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UploadedMediaCloudPersistenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_material_image_persists_in_database_and_serves_after_cache_wipe(): void
    {
        $admin = User::create([
            'name' => 'Admin Media',
            'email' => 'media-admin@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        Category::create([
            'name' => 'ลูกชิ้น',
            'slug' => 'fishball',
            'icon' => 'assets/categories/all.png',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        // 1x1 PNG base64 pixel
        $tinyPngBase64 = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg==';

        $uploadRes = $this->actingAs($admin)->postJson('/admin/price-tracker/upload-image', [
            'image_base64' => $tinyPngBase64,
        ]);

        $uploadRes->assertStatus(200)->assertJson(['success' => true]);
        $mediaUrl = $uploadRes->json('url');
        $this->assertStringStartsWith('/media/ingredients/', $mediaUrl);
        $this->assertDatabaseHas('uploaded_media', [
            'media_key' => ltrim(preg_replace('#^/media/#', '', $mediaUrl), '/'),
        ]);

        // Simulate ephemeral container redeploy/wipe by deleting local cache file
        $cacheFile = storage_path('app/media_cache/' . ltrim(preg_replace('#^/media/#', '', $mediaUrl), '/'));
        if (is_file($cacheFile)) {
            @unlink($cacheFile);
        }
        $this->assertFalse(is_file($cacheFile));

        // GET /media/... still serves image straight from DB and re-warms disk cache
        $serveRes = $this->get($mediaUrl);
        $serveRes->assertStatus(200);
        $this->assertStringStartsWith('image/png', (string) $serveRes->headers->get('Content-Type'));
        $this->assertTrue(is_file($cacheFile));

        // Save material with that /media/... URL and confirm Product sync preserves it
        $matRes = $this->actingAs($admin)->postJson('/admin/price-tracker/materials', [
            'name' => 'กือโปะ',
            'category_slug' => 'fishball',
            'purchase_unit' => 'แพ็ค',
            'latest_cost_price' => 50,
            'yield_sticks_per_unit' => 10,
            'image_url' => $mediaUrl,
        ]);

        $matRes->assertStatus(200);
        $material = RawMaterial::where('name', 'กือโปะ')->firstOrFail();
        $this->assertEquals($mediaUrl, $material->image_url);
        $product = Product::where('raw_material_id', $material->id)->firstOrFail();
        $this->assertStringContainsString($mediaUrl, $product->display_image);
    }
}
