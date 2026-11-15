<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ChatMessage;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PosOnlineOrderSyncTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::create([
            'name' => 'Admin Test',
            'email' => 'admin-pos-sync@malastreet.com',
            'password' => Hash::make('password123'),
        ]);
    }

    public function test_online_order_syncs_to_kitchen_pos_storefront_live_endpoint_and_chat(): void
    {
        $category = Category::create([
            'name' => 'เนื้อสัตว์',
            'slug' => 'meat',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'ลูกชิ้นปลาระเบิด',
            'price_per_stick' => 30,
            'is_available' => true,
            'stock_sticks' => 50,
        ]);

        $order = Order::create([
            'order_no' => 'MS-ONL-20261115-001-3MI',
            'queue_number' => 'G01',
            'order_type' => 'online',
            'customer_name' => 'คุณทดสอบ ออนไลน์',
            'customer_phone' => '089-999-9999',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'promptpay',
            'subtotal' => 30,
            'total_amount' => 30,
            'created_at' => Carbon::now('Asia/Bangkok')->subHours(20),
            'updated_at' => Carbon::now('Asia/Bangkok')->subHours(20),
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_sticks' => 1,
            'unit_price' => 30,
            'subtotal' => 30,
            'cooking_type' => 'grill',
            'spiciness_level' => 'medium',
        ]);

        ChatMessage::create([
            'order_no' => $order->order_no,
            'sender' => 'customer',
            'message' => 'ขอเผ็ดกลางนะครับ',
            'is_read' => false,
        ]);

        // 1. Kitchen Display shows active online order even if created >12h ago (no strict date drop)
        $kitchenRes = $this->actingAs($this->adminUser)->get('/pos/kitchen');
        $kitchenRes->assertStatus(200);
        $kitchenRes->assertSee('G01');
        $kitchenRes->assertSee('คุณทดสอบ ออนไลน์');
        $kitchenRes->assertSee('MS-ONL-20261115-001-3MI');

        // 2. POS Storefront renders live orders card & customer chat drawer
        $posRes = $this->actingAs($this->adminUser)->get('/pos');
        $posRes->assertStatus(200);
        $posRes->assertSee('pos-live-orders-card');
        $posRes->assertSee('pos-chat-drawer');
        $posRes->assertSee('MS-ONL-20261115-001-3MI');

        // 3. Live orders JSON endpoint returns order details + unread chat count
        $liveRes = $this->actingAs($this->adminUser)->getJson('/pos/orders/live');
        $liveRes->assertStatus(200);
        $liveRes->assertJsonFragment([
            'order_no' => 'MS-ONL-20261115-001-3MI',
            'queue_number' => 'G01',
            'order_type' => 'online',
            'customer_name' => 'คุณทดสอบ ออนไลน์',
            'unread_chat' => 1,
        ]);

        // 4. Clicking chat opens /kitchen/chat?order_no=... cleanly
        $chatRes = $this->actingAs($this->adminUser)->get('/kitchen/chat?order_no=' . $order->order_no);
        $chatRes->assertStatus(200);
        $chatRes->assertSee('คุณทดสอบ ออนไลน์');
        $chatRes->assertSee('ขอเผ็ดกลางนะครับ');
    }
}
