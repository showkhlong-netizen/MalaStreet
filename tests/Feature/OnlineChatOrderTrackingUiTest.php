<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnlineChatOrderTrackingUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_online_index_track_button_routes_to_chat_and_chat_renders_red_timeline_and_full_detail_sheet(): void
    {
        $category = Category::create([
            'name' => 'เนื้อสัตว์',
            'slug' => 'meat',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'หมูสามชั้นสไลด์',
            'price_per_stick' => 15,
            'is_available' => true,
            'stock_sticks' => 50,
        ]);

        $order = Order::create([
            'order_no' => 'MS-ONL-20261115-001-DYW',
            'queue_number' => 'G01',
            'order_type' => 'online',
            'customer_name' => 'คุณทดสอบ',
            'customer_phone' => '089-999-9999',
            'pickup_time' => '18:30',
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'payment_method' => 'promptpay',
            'subtotal' => 30,
            'total_amount' => 30,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'product_name' => $product->name,
            'quantity_sticks' => 2,
            'unit_price' => 15,
            'subtotal' => 30,
            'cooking_type' => 'grill',
            'grill_style' => 'dip',
            'spiciness_level' => 'medium',
        ]);

        // 1. Online storefront sets success-track-link to online.chat
        $indexRes = $this->get('/order');
        $indexRes->assertStatus(200);
        $indexRes->assertSee('ติดตามสถานะออเดอร์เรียลไทม์');
        $indexRes->assertSee(route('online.chat'), false);

        // 2. Customer Chat page replaces 'สถานะ' with 'ติดตามออเดอร์', renders red-themed timeline strip + full detail sheet
        $chatRes = $this->get('/order/chat?order_no=' . $order->order_no);
        $chatRes->assertStatus(200);
        $chatRes->assertSee('ติดตามออเดอร์');
        $chatRes->assertSee('order-timeline-strip');
        $chatRes->assertSee('order-detail-sheet');
        $chatRes->assertSee('Order no. ' . $order->order_no);
        $chatRes->assertSee('ร้านรับออเดอร์แล้ว');
        $chatRes->assertSee('หมูสามชั้นสไลด์');
        $chatRes->assertSee('ดูเพิ่มเติม');
        $chatRes->assertSee('#D90012');

        // 3. Status JSON endpoint updates live status for the timeline
        $order->update(['status' => 'cooking']);
        $statusRes = $this->getJson('/order/status/' . $order->order_no);
        $statusRes->assertStatus(200);
        $statusRes->assertJsonFragment([
            'status' => 'cooking',
            'queue_number' => 'G01',
        ]);
    }

    public function test_online_chat_renders_cleanly_when_no_orders_exist(): void
    {
        $chatRes = $this->get('/order/chat');
        $chatRes->assertStatus(200);
        $chatRes->assertSee('ยังไม่ได้เลือกออเดอร์เชื่อมต่อ');
        $chatRes->assertDontSee('id="order-timeline-strip"', false);
        $chatRes->assertDontSee('id="order-detail-sheet"', false);
    }
}
