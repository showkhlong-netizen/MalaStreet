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

    public function test_get_customer_profile_checks_ip_on_entry_and_recovers_via_cloud_device_key_when_ip_changes(): void
    {
        $proxyHeaders = [
            'REMOTE_ADDR' => '10.0.0.5',
            'HTTP_X_REAL_IP' => '10.20.30.40',
            'HTTP_X_FORWARDED_FOR' => '10.0.0.5, 183.88.52.188',
        ];

        $saveRes = $this->withServerVariables($proxyHeaders)->postJson('/order/customer-profile', [
            'customer_name' => 'คุณต้น',
            'customer_phone' => '086-555-7777',
            'customer_address' => '88/9 หมู่บ้านสุขใจ',
            'device_key' => 'ms_device_cloud_test_01',
        ]);
        $saveRes->assertStatus(200)->assertJsonFragment([
            'success' => true,
            'ip' => '183.88.52.188',
        ]);

        // Immediate IP check on /order entry from same IP behind Render proxy
        $checkSameIp = $this->withServerVariables($proxyHeaders)
            ->getJson('/order/customer-profile?device_key=ms_device_cloud_test_01');
        $checkSameIp->assertStatus(200)->assertJson([
            'success' => true,
            'ip' => '183.88.52.188',
            'found' => true,
            'profile' => [
                'customer_name' => 'คุณต้น',
                'customer_phone' => '086-555-7777',
                'customer_address' => '88/9 หมู่บ้านสุขใจ',
            ],
        ]);

        // Customer switches to 5G/new IP -> still recovers from Cloud DB via device_key and re-binds to new IP
        $newIpHeaders = ['REMOTE_ADDR' => '49.228.10.25'];
        $checkNewIp = $this->withServerVariables($newIpHeaders)
            ->getJson('/order/customer-profile?device_key=ms_device_cloud_test_01');
        $checkNewIp->assertStatus(200)->assertJson([
            'success' => true,
            'ip' => '49.228.10.25',
            'found' => true,
            'profile' => [
                'customer_name' => 'คุณต้น',
                'customer_phone' => '086-555-7777',
                'customer_address' => '88/9 หมู่บ้านสุขใจ',
            ],
        ]);

        // Subsequent /order load on the new IP even without device_key query now resolves directly by new IP
        $reloadedNewIp = $this->withServerVariables($newIpHeaders)->get('/order');
        $reloadedNewIp->assertStatus(200);
        $reloadedNewIp->assertSee('value="คุณต้น"', false);
        $reloadedNewIp->assertSee('value="086-555-7777"', false);
    }
}
