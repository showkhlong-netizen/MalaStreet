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

        // 1. Kitchen Display shows active online order + top channel tabs
        $kitchenRes = $this->actingAs($this->adminUser)->get('/pos/kitchen');
        $kitchenRes->assertStatus(200);
        $kitchenRes->assertSee('G01');
        $kitchenRes->assertSee('คุณทดสอบ ออนไลน์');
        $kitchenRes->assertSee('MS-ONL-20261115-001-3MI');
        $kitchenRes->assertSee('kitchen-channel-tabs');
        $kitchenRes->assertSee('สั่งรับหน้าร้าน (ออนไลน์)');
        $kitchenRes->assertSee('Delivery (จัดส่ง)');
        $kitchenRes->assertSee('หน้าร้าน (POS)');
        $kitchenRes->assertSee('data-channel="online_pickup"', false);

        // 2. POS Storefront no longer renders live orders card or customer chat drawer
        $posRes = $this->actingAs($this->adminUser)->get('/pos');
        $posRes->assertStatus(200);
        $posRes->assertDontSee('pos-live-orders-card');
        $posRes->assertDontSee('pos-chat-drawer');
        $posRes->assertDontSee('ออเดอร์ออนไลน์ & หน้าร้าน');

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

    public function test_kitchen_separates_delivery_pickup_online_and_pos_channels(): void
    {
        Order::create([
            'order_no' => 'MS-DLV-001',
            'queue_number' => 'D01',
            'order_type' => 'online',
            'customer_name' => 'ลูกค้าเดลิเวอรี่',
            'pickup_time' => 'Delivery จัดส่งถึงบ้าน',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'promptpay',
            'subtotal' => 60,
            'total_amount' => 60,
        ]);

        Order::create([
            'order_no' => 'MS-POS-001',
            'queue_number' => 'A01',
            'order_type' => 'pos',
            'customer_name' => 'ลูกค้าหน้าร้าน',
            'status' => 'cooking',
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'subtotal' => 45,
            'total_amount' => 45,
        ]);

        $res = $this->actingAs($this->adminUser)->get('/pos/kitchen');
        $res->assertStatus(200);
        $res->assertSee('data-channel="delivery"', false);
        $res->assertSee('data-channel="pos"', false);
        $res->assertSee('D01');
        $res->assertSee('A01');
    }
}
