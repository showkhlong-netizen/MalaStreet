<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PriceTrackerCategorySyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_updating_material_category_syncs_price_tracker_products_pos_and_online(): void
    {
        $admin = User::create([
            'name' => 'Admin Sync',
            'email' => 'sync-admin@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        $catPorkChicken = Category::create([
            'name' => 'เนื้อสัตว์ & ไก่',
            'slug' => 'pork-chicken',
            'icon' => '🍗',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $catVegetables = Category::create([
            'name' => 'ผัก',
            'slug' => 'vegetables',
            'icon' => '🥬',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        $material = RawMaterial::create([
            'name' => 'เห็ดเข็มทองสด',
            'category_slug' => 'pork-chicken',
            'default_vendor' => 'ตลาดเช้า',
            'purchase_unit' => 'แพ็ค',
            'yield_sticks_per_unit' => 10,
            'latest_cost_price' => 45.00,
            'cost_per_stick' => 4.50,
            'current_stock_sticks' => 20,
        ]);

        PriceHistory::create([
            'raw_material_id' => $material->id,
            'vendor_name' => 'ตลาดเช้า',
            'purchase_unit_price' => 45.00,
            'purchase_unit' => 'แพ็ค',
            'yield_sticks_per_unit' => 10,
            'cost_per_stick' => 4.50,
            'recorded_at' => Carbon::today(),
        ]);

        $product = Product::create([
            'category_id' => $catPorkChicken->id,
            'raw_material_id' => $material->id,
            'name' => 'เห็ดเข็มทองสด',
            'price_per_stick' => 10.00,
            'stock_sticks' => 20,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        // Change category in Edit Modal (Image 1) from pork-chicken -> vegetables
        $updateRes = $this->actingAs($admin)->postJson('/admin/price-tracker/materials/' . $material->id, [
            'name' => 'เห็ดเข็มทองสด',
            'category_slug' => 'vegetables',
            'purchase_unit' => 'แพ็ค',
            'latest_cost_price' => 45.00,
            'yield_sticks_per_unit' => 10,
            'cost_per_stick' => 4.50,
            'default_vendor' => 'ตลาดเช้า',
        ]);

        $updateRes->assertStatus(200)
            ->assertJson([
                'success' => true,
                'material' => [
                    'id' => $material->id,
                    'category_slug' => 'vegetables',
                ],
            ]);

        // DB synced for BOTH RawMaterial AND linked Product
        $material->refresh();
        $product->refresh();
        $this->assertSame('vegetables', $material->category_slug);
        $this->assertSame($catVegetables->id, $product->category_id);

        // Price Tracker HTML matches between filter pills (data-cats) and edit modal (category_slug)
        $trackerRes = $this->actingAs($admin)->get('/admin/price-tracker');
        $trackerRes->assertStatus(200);
        $html = $trackerRes->getContent();
        $this->assertStringContainsString('data-cats="vegetables"', $html);
        $this->assertStringNotContainsString('data-cats="vegetables,pork-chicken"', $html);
        $this->assertStringContainsString('"category_slug":"vegetables"', $html);

        // Price Tracker JSON endpoint returns category_slug
        $trackerJson = $this->actingAs($admin)->getJson('/admin/price-tracker?json=1');
        $trackerJson->assertStatus(200);
        $matchedMat = collect($trackerJson->json('materials'))->firstWhere('id', $material->id);
        $this->assertNotNull($matchedMat);
        $this->assertSame('vegetables', $matchedMat['category_slug']);

        // POS & Online Storefront JSON reflect the new category immediately
        $posJson = $this->getJson('/pos?json=1');
        $posJson->assertStatus(200);
        $posProduct = collect($posJson->json('products'))->firstWhere('id', $product->id);
        $this->assertNotNull($posProduct);
        $this->assertSame('vegetables', $posProduct['category_slug']);

        $onlineJson = $this->getJson('/order?categories_json=1');
        $onlineJson->assertStatus(200);
        $onlineProduct = collect($onlineJson->json('products'))->firstWhere('id', $product->id);
        $this->assertNotNull($onlineProduct);
        $this->assertSame('vegetables', $onlineProduct['category_slug']);
    }

    public function test_price_tracker_excludes_stale_product_category_and_normalizes_uncategorized_slug(): void
    {
        $admin = User::create([
            'name' => 'Admin Sync Two',
            'email' => 'sync-admin2@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        $catBeef = Category::create([
            'name' => 'เนื้อโคขุน',
            'slug' => 'beef',
            'icon' => '🥩',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        Category::create([
            'name' => 'ผัก',
            'slug' => 'vegetables',
            'icon' => '🥬',
            'sort_order' => 2,
            'is_active' => true,
        ]);

        // Case A: Material explicitly assigned to 'vegetables', while linked Product still had stale 'beef'
        $matWithStaleProduct = RawMaterial::create([
            'name' => 'ผักบุ้งจีน',
            'category_slug' => 'vegetables',
            'purchase_unit' => 'กำ',
            'yield_sticks_per_unit' => 5,
            'latest_cost_price' => 20.00,
            'cost_per_stick' => 4.00,
            'current_stock_sticks' => 10,
        ]);
        PriceHistory::create([
            'raw_material_id' => $matWithStaleProduct->id,
            'vendor_name' => 'ตลาดเช้า',
            'purchase_unit_price' => 20.00,
            'purchase_unit' => 'กำ',
            'yield_sticks_per_unit' => 5,
            'cost_per_stick' => 4.00,
            'recorded_at' => Carbon::today(),
        ]);
        Product::create([
            'category_id' => $catBeef->id,
            'raw_material_id' => $matWithStaleProduct->id,
            'name' => 'ผักบุ้งจีน',
            'price_per_stick' => 10.00,
            'stock_sticks' => 10,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        // Case B: Material previously stored with 'uncategorized'
        $matUncategorized = RawMaterial::create([
            'name' => 'ผักกาดขาว',
            'category_slug' => 'uncategorized',
            'purchase_unit' => 'หัว',
            'yield_sticks_per_unit' => 8,
            'latest_cost_price' => 32.00,
            'cost_per_stick' => 4.00,
            'current_stock_sticks' => 8,
        ]);
        PriceHistory::create([
            'raw_material_id' => $matUncategorized->id,
            'vendor_name' => 'ตลาดเช้า',
            'purchase_unit_price' => 32.00,
            'purchase_unit' => 'หัว',
            'yield_sticks_per_unit' => 8,
            'cost_per_stick' => 4.00,
            'recorded_at' => Carbon::today(),
        ]);

        $res = $this->actingAs($admin)->get('/admin/price-tracker');
        $res->assertStatus(200);
        $html = $res->getContent();

        $this->assertStringNotContainsString('data-cats="vegetables,beef"', $html);
        $this->assertStringNotContainsString('data-cats="uncategorized"', $html);
        $this->assertStringContainsString('data-cats="vegetables"', $html);
    }
}
