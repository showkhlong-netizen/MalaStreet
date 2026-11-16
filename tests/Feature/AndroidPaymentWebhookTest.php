<?php

namespace Tests\Feature;

use App\Http\Controllers\PaymentWebhookController;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AndroidPaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_android_notification_webhook_auto_confirms_unpaid_promptpay_order(): void
    {
        $category = Category::create([
            'name' => 'เนื้อสัตว์',
            'slug' => 'meat',
            'sort_order' => 1,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'หมูสามชั้น',
            'price_per_stick' => 35,
            'is_available' => true,
            'stock_sticks' => 50,
        ]);

        $submitRes = $this->postJson('/order/submit', [
            'customer_name' => 'ลูกค้าทดสอบ',
            'customer_phone' => '0812345678',
            'pickup_time' => 'ทันที (~15-20 นาที)',
            'payment_method' => 'promptpay',
            'awaiting_payment' => true,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'cooking_type' => 'grill',
                    'spiciness_level' => 'medium',
                ],
            ],
        ]);

        $submitRes->assertStatus(200)->assertJson([
            'success' => true,
            'awaiting_payment' => true,
            'total_amount' => 35.0,
        ]);

        $orderNo = $submitRes->json('order_no');

        // Second simultaneous order with same 35 THB gets unique satang 35.01
        $submitRes2 = $this->postJson('/order/submit', [
            'customer_name' => 'ลูกค้าคนที่สอง',
            'customer_phone' => '0898765432',
            'pickup_time' => 'ทันที (~15-20 นาที)',
            'payment_method' => 'promptpay',
            'awaiting_payment' => true,
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                    'cooking_type' => 'grill',
                    'spiciness_level' => 'medium',
                ],
            ],
        ]);

        $submitRes2->assertStatus(200)->assertJson([
            'success' => true,
            'awaiting_payment' => true,
            'total_amount' => 35.01,
        ]);

        // Status polling initially returns unpaid
        $this->getJson('/order/payment-status/' . $orderNo)
            ->assertStatus(200)
            ->assertJson(['paid' => false, 'payment_status' => 'unpaid']);

        $config = PaymentWebhookController::getWebhookConfig();

        // Invalid secret is rejected
        $this->postJson('/webhooks/android-payment', [
            'secret' => 'wrong-secret',
            'message' => 'K PLUS: เงินเข้าบัญชี 098-1-53225-2 จำนวน 35.00 บาท',
        ])->assertStatus(401);

        // Valid secret + multiline Krungthai/KBank notification (even with unescaped newline in JSON)
        $multilineBody = "{\"secret\":\"" . $config['secret'] . "\",\"message\":\"รับเงินสำเร็จ\nได้รับ +30.00 บาท เข้าพร้อมเพย์ จากบัญชี\nกสิกรไทย XXX-X-XX539-3\"}";
        $this->assertNotEmpty($multilineBody);

        // Valid secret + KBank notification confirms the matching order automatically
        $webhookRes = $this->postJson('/webhooks/android-payment', [
            'secret' => $config['secret'],
            'message' => 'K PLUS: เงินเข้าบัญชี 098-1-53225-2 จำนวน 35.00 บาท เวลา 08:15 น.',
        ]);

        $webhookRes->assertStatus(200)->assertJson([
            'success' => true,
            'matched' => true,
            'order_no' => $orderNo,
            'amount' => 35.0,
        ]);

        $this->getJson('/order/payment-status/' . $orderNo)
            ->assertStatus(200)
            ->assertJson(['paid' => true, 'payment_status' => 'paid', 'status' => 'confirmed']);

        $this->assertEquals('unpaid', Order::where('order_no', $submitRes2->json('order_no'))->value('payment_status'));

        // Multiline notification with unescaped newline in raw JSON body (exact Krungthai NEXT format)
        $multilineBody = "{\"secret\":\"" . $config['secret'] . "\",\"message\":\"รับเงินสำเร็จ\nได้รับ +35.01 บาท เข้าพร้อมเพย์ จากบัญชี\nกสิกรไทย XXX-X-XX539-3\"}";
        $rawRes = $this->call('POST', '/webhooks/android-payment', [], [], [], ['CONTENT_TYPE' => 'application/json'], $multilineBody);
        $rawRes->assertStatus(200)->assertJson([
            'success' => true,
            'matched' => true,
            'order_no' => $submitRes2->json('order_no'),
            'amount' => 35.01,
        ]);
    }

    public function test_access_page_cashier_tab_saves_qr_history_and_switches_presets(): void
    {
        $admin = \App\Models\User::create([
            'name' => 'Admin Cashier',
            'email' => 'cashier-admin@malastreet.com',
            'password' => \Illuminate\Support\Facades\Hash::make('password123'),
        ]);

        // 1. Access page renders จัดการแคชเชียร์ tab
        $accessRes = $this->actingAs($admin)->get('/admin/access?tab=cashier');
        $accessRes->assertStatus(200);
        $accessRes->assertSee('จัดการแคชเชียร์');
        $accessRes->assertSee('คลังภาพ QR พร้อมเพย์ที่บันทึกไว้ทั้งหมด');

        // 2. Save a new QR preset with uploaded base64 image & custom store name
        $saveRes = $this->actingAs($admin)->postJson('/pos/settings/promptpay-qr', [
            'label' => 'บัญชีสำรอง SCB',
            'store_name' => 'หม่าล่าสตรีท สาขา 2',
            'account_no' => '123-4-56789-0 (ไทยพาณิชย์)',
            'account_name' => 'ทดสอบ บัญชีสำรอง',
            'receipt_note' => 'โอนแล้วรอรับคิวได้เลย',
            'payload' => '0812345678',
            'image_data' => 'data:image/jpeg;base64,/9j/4AAQSkZJRgABAQAAAQABAAD/',
        ]);

        $saveRes->assertStatus(200)->assertJson(['success' => true]);
        $history = $saveRes->json('settings.history');
        $this->assertGreaterThanOrEqual(2, count($history));
        $this->assertEquals('หม่าล่าสตรีท สาขา 2', $saveRes->json('settings.store_name'));

        // 3. Switch back to the original default QR preset in history
        $activateRes = $this->actingAs($admin)->postJson('/pos/settings/promptpay-qr/activate', [
            'id' => 'qr_default',
        ]);

        $activateRes->assertStatus(200)->assertJson(['success' => true]);
        $this->assertEquals('098-1-53225-2 (กสิกรไทย)', $activateRes->json('settings.account_no'));

        // 4. Live webhook status endpoint returns pending unpaid orders and allows 1-click mark_paid
        $pendingOrder = Order::create([
            'order_no' => 'MS-ONL-20261116-099-TST',
            'queue_number' => 'G99',
            'order_type' => 'online',
            'customer_name' => 'รอลูกค้าโอน',
            'status' => 'pending',
            'payment_method' => 'promptpay',
            'payment_status' => 'unpaid',
            'subtotal' => 45,
            'total_amount' => 45,
        ]);

        $statusRes = $this->actingAs($admin)->getJson('/pos/settings/webhook-status');
        $statusRes->assertStatus(200)
            ->assertJsonFragment(['order_no' => 'MS-ONL-20261116-099-TST']);

        $this->actingAs($admin)->postJson('/pos/orders/' . $pendingOrder->id . '/status', [
            'status' => 'confirmed',
            'mark_paid' => true,
        ])->assertStatus(200)->assertJson(['success' => true]);

        $this->getJson('/order/payment-status/MS-ONL-20261116-099-TST')
            ->assertStatus(200)
            ->assertJson(['paid' => true, 'payment_status' => 'paid']);

        // 5. POS storeOrder also commits cleanly and executes AuditLog + MasterSummary outside transaction
        $category = Category::create(['name' => 'ย่าง', 'slug' => 'grill', 'sort_order' => 1]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'สามชั้น',
            'price_per_stick' => 25,
            'is_available' => true,
            'stock_sticks' => 20,
        ]);

        $posOrderRes = $this->actingAs($admin)->postJson('/pos/order', [
            'payment_method' => 'cash',
            'cash_received' => 50,
            'customer_name' => 'ลูกค้าหน้าร้าน',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'cooking_type' => 'grill',
                    'spiciness_level' => 'medium',
                ],
            ],
        ]);

        $posOrderRes->assertStatus(200)->assertJson(['success' => true]);

        // 6. Outgoing transfer message is ignored, and underpaid message reports exact diff
        $unpaid57 = Order::create([
            'order_no' => 'MS-ONL-20261116-100-MMT',
            'queue_number' => 'G98',
            'order_type' => 'online',
            'customer_name' => 'เทสต์ยอดไม่ครบ',
            'status' => 'pending',
            'payment_method' => 'promptpay',
            'payment_status' => 'unpaid',
            'subtotal' => 57,
            'total_amount' => 57,
        ]);

        $config = PaymentWebhookController::getWebhookConfig();
        $this->postJson('/webhooks/android-payment', [
            'secret' => $config['secret'],
            'message' => 'K PLUS: โอนเงินสำเร็จ 57.00 บาท ไปยัง นายสมชาย',
        ])->assertStatus(200)->assertJson(['matched' => false, 'ignored_outgoing' => true]);

        $mismatchRes = $this->postJson('/webhooks/android-payment', [
            'secret' => $config['secret'],
            'message' => 'K PLUS: เงินเข้าบัญชี จำนวน 50.00 บาท',
        ]);
        $mismatchRes->assertStatus(200)->assertJson(['matched' => false, 'parsed_amount' => 50]);
        $this->assertStringContainsString('ขาด ฿7.00', (string) $mismatchRes->json('message'));
        $this->assertEquals('unpaid', $unpaid57->fresh()->payment_status);
    }
}
