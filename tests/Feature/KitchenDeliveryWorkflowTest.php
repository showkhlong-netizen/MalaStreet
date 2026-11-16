<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class KitchenDeliveryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function seedAdminAndProducts(): array
    {
        $admin = User::create([
            'name' => 'Kitchen Admin',
            'email' => 'kitchen-admin@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        $cat = Category::create([
            'name' => 'เนื้อสัตว์',
            'slug' => 'meat',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        $p1 = Product::create([
            'category_id' => $cat->id,
            'name' => 'หมูสามชั้นสไลซ์',
            'price_per_stick' => 15.00,
            'stock_sticks' => 50,
            'is_available' => true,
            'sort_order' => 1,
        ]);

        $p2 = Product::create([
            'category_id' => $cat->id,
            'name' => 'เห็ดเข็มทองพันเบคอน',
            'price_per_stick' => 20.00,
            'stock_sticks' => 30,
            'is_available' => true,
            'sort_order' => 2,
        ]);

        return [$admin, $p1, $p2];
    }

    public function test_delivery_order_3_step_workflow_edit_timer_and_split_completion(): void
    {
        [$admin, $p1, $p2] = $this->seedAdminAndProducts();

        $order = Order::create([
            'order_no' => 'MS-ONL-20261116-501-DLV',
            'queue_number' => 'G10',
            'order_type' => 'online',
            'customer_name' => 'คุณเดลิเวอรี่',
            'customer_phone' => '081-222-3333',
            'pickup_time' => 'เดลิเวอรี่: 99/1 ถนนสุขุมวิท',
            'status' => 'confirmed',
            'payment_method' => 'promptpay',
            'payment_status' => 'paid',
            'subtotal' => 30.00,
            'total_amount' => 30.00,
        ]);

        $order->items()->create([
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'quantity_sticks' => 2,
            'unit_price' => 15.00,
            'subtotal' => 30.00,
            'cooking_type' => 'grill',
            'grill_style' => 'brush',
            'spiciness_level' => 'medium',
        ]);

        // Initial Step 1 UI shows รับออร์เดอร์, แก้ไข, and PIN cancel button
        $resStep1 = $this->actingAs($admin)->get('/pos/kitchen');
        $resStep1->assertStatus(200);
        $resStep1->assertSee('รับออร์เดอร์');
        $resStep1->assertSee('แก้ไข');
        $resStep1->assertSee('openPinCancelModal(' . $order->id . ')', false);

        // Step 1 Edit Order Items (add item p2 and recalculate total)
        $editRes = $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/items', [
            'items' => [
                [
                    'product_id' => $p1->id,
                    'product_name' => $p1->name,
                    'quantity_sticks' => 2,
                    'unit_price' => 15,
                    'cooking_type' => 'grill',
                    'grill_style' => 'brush',
                    'spiciness_level' => 'medium',
                ],
                [
                    'product_id' => $p2->id,
                    'product_name' => $p2->name,
                    'quantity_sticks' => 1,
                    'unit_price' => 20,
                    'cooking_type' => 'soup',
                    'soup_style' => 'mala_rich',
                    'spiciness_level' => 'hot',
                ],
            ],
            'pickup_time' => 'เดลิเวอรี่: 99/1 ถนนสุขุมวิท',
            'notes' => 'แยกน้ำจิ้มเพิ่ม',
        ]);
        $editRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals(50.00, (float) $order->fresh()->total_amount);
        $this->assertEquals(29, $p2->fresh()->stock_sticks);

        // Accept -> Step 2
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/kitchen-step', [
            'action' => 'accept',
        ])->assertStatus(200)->assertJson(['success' => true, 'kitchen_step' => 2]);

        $resStep2 = $this->actingAs($admin)->get('/pos/kitchen');
        $resStep2->assertStatus(200);
        $resStep2->assertSee('หม่าล่าย่างเผ็ดกลาง');
        $resStep2->assertSee('น้ำซุปหม่าล่า');
        $resStep2->assertSee('x2');
        $resStep2->assertSee('x1');
        $resStep2->assertSee('เริ่มทำออร์เดอร์');
        $resStep2->assertSee('พร้อมส่ง');
        $resStep2->assertSee("updateDeliveryStep(" . $order->id . ", 'back_to_step1')", false);

        // Start cooking -> 10-min countdown bar replaces button
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/kitchen-step', [
            'action' => 'start_cooking',
        ])->assertStatus(200)->assertJson(['success' => true, 'kitchen_step' => 2, 'status' => 'cooking']);

        $this->assertNotNull($order->fresh()->cooking_started_at);
        $resStep2Timer = $this->actingAs($admin)->get('/pos/kitchen');
        $resStep2Timer->assertStatus(200);
        $resStep2Timer->assertSee('delivery-countdown-pill');

        // Back to Step 1 works cleanly
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/kitchen-step', [
            'action' => 'back_to_step1',
        ])->assertStatus(200)->assertJson(['success' => true, 'kitchen_step' => 1]);
        $this->assertNull($order->fresh()->cooking_started_at);

        // Advance back to Step 2 -> Mark Ready -> Step 3
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/kitchen-step', ['action' => 'accept'])->assertStatus(200);
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/kitchen-step', ['action' => 'mark_ready'])
            ->assertStatus(200)
            ->assertJson(['success' => true, 'kitchen_step' => 3, 'status' => 'ready']);

        $resStep3 = $this->actingAs($admin)->get('/pos/kitchen');
        $resStep3->assertStatus(200);
        $resStep3->assertSee('ไปที่โลเคชั่น');
        $resStep3->assertSee('ส่งออร์เดอร์สำเร็จ');

        // Complete Delivery with split payment + note
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/complete-delivery', [
            'cash_amount' => 20.00,
            'transfer_amount' => 30.00,
            'delivery_note' => 'ส่งหน้าบ้าน ฝากไว้ที่รปภ.',
        ])->assertStatus(200)->assertJson(['success' => true, 'status' => 'completed']);

        $order->refresh();
        $this->assertSame('completed', $order->status);
        $this->assertEquals(20.00, (float) $order->paid_cash_amount);
        $this->assertEquals(30.00, (float) $order->paid_transfer_amount);
        $this->assertSame('ส่งหน้าบ้าน ฝากไว้ที่รปภ.', $order->delivery_note);
    }

    public function test_pin_1122_required_to_cancel_delivery_order_and_restores_stock(): void
    {
        [$admin, $p1] = $this->seedAdminAndProducts();

        $order = Order::create([
            'order_no' => 'MS-ONL-20261116-502-DLV',
            'queue_number' => 'G11',
            'order_type' => 'online',
            'customer_name' => 'คุณขอยกเลิก',
            'pickup_time' => 'เดลิเวอรี่: 12/3 ซอยอารีย์',
            'status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'subtotal' => 45.00,
            'total_amount' => 45.00,
        ]);

        $order->items()->create([
            'product_id' => $p1->id,
            'product_name' => $p1->name,
            'quantity_sticks' => 3,
            'unit_price' => 15.00,
            'subtotal' => 45.00,
            'cooking_type' => 'grill',
            'spiciness_level' => 'medium',
        ]);

        // Wrong PIN rejected
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/cancel-with-pin', [
            'pin' => '9999',
        ])->assertStatus(422)->assertJson(['success' => false]);
        $this->assertSame('pending', $order->fresh()->status);

        // Correct PIN 1122 cancels order and restores 3 sticks
        $this->actingAs($admin)->postJson('/pos/orders/' . $order->id . '/cancel-with-pin', [
            'pin' => '1122',
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->assertSame('cancelled', $order->fresh()->status);
        $this->assertEquals(53, $p1->fresh()->stock_sticks);
    }
}
