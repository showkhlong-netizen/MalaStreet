<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineCustomerIpProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_name_phone_auto_saves_and_overwrites_by_client_ip(): void
    {
        $clientHeaders = ['REMOTE_ADDR' => '183.88.52.152'];

        // 1. Auto-save initial profile for IP 183.88.52.152
        $saveRes = $this->withServerVariables($clientHeaders)->postJson('/order/customer-profile', [
            'customer_name' => 'คุณกานต์',
            'customer_phone' => '081-111-2222',
            'customer_address' => '99/1 ถนนสีลม',
        ]);
        $saveRes->assertStatus(200);
        $saveRes->assertJsonFragment([
            'success' => true,
            'ip' => '183.88.52.152',
            'customer_name' => 'คุณกานต์',
            'customer_phone' => '081-111-2222',
        ]);

        // 2. Reload /order from same IP -> pre-filled automatically
        $pageRes = $this->withServerVariables($clientHeaders)->get('/order');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('value="คุณกานต์"', false);
        $pageRes->assertSee('value="081-111-2222"', false);
        $pageRes->assertSee('99/1 ถนนสีลม');

        // 3. Edit name & phone -> immediately overwrites latest values for this IP
        $editRes = $this->withServerVariables($clientHeaders)->postJson('/order/customer-profile', [
            'customer_name' => 'คุณกานต์ (อัปเดตใหม่)',
            'customer_phone' => '089-999-8888',
            'customer_address' => '123 ซอยอารีย์',
        ]);
        $editRes->assertStatus(200);

        $reloadedRes = $this->withServerVariables($clientHeaders)->get('/order');
        $reloadedRes->assertStatus(200);
        $reloadedRes->assertSee('value="คุณกานต์ (อัปเดตใหม่)"', false);
        $reloadedRes->assertSee('value="089-999-8888"', false);
        $reloadedRes->assertDontSee('081-111-2222');

        // 4. Different IP does not see first IP's profile
        $otherIpRes = $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.55'])->get('/order');
        $otherIpRes->assertStatus(200);
        $otherIpRes->assertDontSee('คุณกานต์ (อัปเดตใหม่)');
        $otherIpRes->assertDontSee('089-999-8888');
    }

    public function test_submitting_online_order_updates_saved_ip_profile(): void
    {
        $category = Category::create([
            'name' => 'เนื้อสัตว์',
            'slug' => 'meat',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'เบคอนพันเห็ด',
            'price_per_stick' => 15,
            'is_available' => true,
            'stock_sticks' => 50,
        ]);

        $clientHeaders = ['REMOTE_ADDR' => '183.88.52.200'];

        $submitRes = $this->withServerVariables($clientHeaders)->postJson('/order/submit', [
            'customer_name' => 'คุณพลอย',
            'customer_phone' => '082-333-4444',
            'customer_address' => 'คอนโดลุมพินี',
            'pickup_time' => '18:00 น.',
            'payment_method' => 'cash',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'cooking_type' => 'grill',
                    'spiciness_level' => 'medium',
                ],
            ],
        ]);
        $submitRes->assertStatus(200)->assertJsonFragment(['success' => true]);

        $pageRes = $this->withServerVariables($clientHeaders)->get('/order');
        $pageRes->assertStatus(200);
        $pageRes->assertSee('value="คุณพลอย"', false);
        $pageRes->assertSee('value="082-333-4444"', false);
    }
}
