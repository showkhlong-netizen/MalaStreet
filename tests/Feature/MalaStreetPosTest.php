<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\PriceHistory;
use App\Models\Product;
use App\Models\RawMaterial;
use App\Models\Receipt;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MalaStreetPosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_pos_screen_renders_successfully(): void
    {
        $response = $this->get('/pos');
        $response->assertStatus(200);
        $response->assertSee('แคชเชียร์หน้าร้าน');
        $response->assertSee('หม่าล่าสตรีท');
    }

    public function test_pos_order_submission_with_grill_and_spiciness(): void
    {
        $product = Product::where('name', 'like', '%เอ็นไก่%')->firstOrFail();
        $initialStock = $product->stock_sticks;

        $response = $this->postJson('/pos/order', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 2,
                    'cooking_type' => 'grill',
                    'grill_style' => 'brush',
                    'soup_style' => null,
                    'spiciness_level' => 'medium',
                    'special_instructions' => 'ทาซอสฉ่ำๆ',
                ]
            ],
            'payment_method' => 'cash',
            'cash_received' => 100,
            'customer_name' => 'ลูกค้าหน้าร้าน โต๊ะ 1',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        // Assert stock decremented
        $product->refresh();
        $this->assertEquals($initialStock - 2, $product->stock_sticks);

        // Assert order was created
        $this->assertDatabaseHas('orders', [
            'customer_name' => 'ลูกค้าหน้าร้าน โต๊ะ 1',
            'order_type' => 'pos',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity_sticks' => 2,
            'cooking_type' => 'grill',
            'grill_style' => 'brush',
            'spiciness_level' => 'medium',
        ]);
    }

    public function test_kitchen_display_and_status_transition(): void
    {
        $order = Order::firstOrFail();

        $response = $this->get('/pos/kitchen');
        $response->assertStatus(200);
        $response->assertSee($order->queue_number);

        // Update status to cooking
        $statusResp = $this->postJson("/pos/orders/{$order->id}/status", [
            'status' => 'cooking',
        ]);
        $statusResp->assertStatus(200);
        $this->assertEquals('cooking', $order->fresh()->status);
    }

    public function test_online_order_grab_style_submission_and_tracking(): void
    {
        $product = Product::where('name', 'like', '%ฟองเต้าหู้%')->firstOrFail();

        $orderPayload = [
            'customer_name' => 'คุณทดสอบ',
            'customer_phone' => '081-999-8888',
            'pickup_time' => '18:30 น.',
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 3,
                    'cooking_type' => 'soup',
                    'grill_style' => null,
                    'soup_style' => 'mala_rich',
                    'spiciness_level' => 'hot',
                    'special_instructions' => 'ซุปหม่าล่าน้ำข้นเผ็ดมาก',
                ]
            ],
            'notes' => 'สั่งออนไลน์มารับเอง',
        ];

        $response = $this->postJson('/order/submit', $orderPayload);
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $orderNo = $response->json('order_no');
        $this->assertNotNull($orderNo);

        // Check tracking screen
        $trackResp = $this->get("/order/track/{$orderNo}");
        $trackResp->assertStatus(200);
        $trackResp->assertSee('คุณทดสอบ');

        // Check live status json
        $statusResp = $this->get("/order/status/{$orderNo}");
        $statusResp->assertStatus(200);
        $statusResp->assertJson(['status' => 'confirmed']);
    }

    public function test_receipt_scan_and_yield_conversion(): void
    {
        $rawMaterial = RawMaterial::where('name', 'like', '%ไส้กรอกไก่%')->firstOrFail();
        $initialStock = $rawMaterial->current_stock_sticks;

        // Test receipt with 1 pack yield 10 sticks at 65 THB
        $response = $this->postJson('/admin/receipts', [
            'receipt_no' => 'TEST-MK-999',
            'vendor_name' => 'สยามแม็คโคร',
            'purchase_date' => Carbon::today()->toDateString(),
            'total_amount' => 65.00,
            'items' => [
                [
                    'item_name' => $rawMaterial->name,
                    'raw_material_id' => $rawMaterial->id,
                    'quantity' => 1,
                    'unit' => 'แพ็ก',
                    'unit_price' => 65.00,
                    'yield_sticks_per_unit' => 10,
                    'add_to_inventory' => true,
                ]
            ]
        ]);

        $response->assertStatus(200);

        // Verify PriceHistory was recorded
        $this->assertDatabaseHas('price_histories', [
            'raw_material_id' => $rawMaterial->id,
            'vendor_name' => 'สยามแม็คโคร',
            'purchase_unit_price' => 65.00,
            'cost_per_stick' => 6.50,
        ]);
    }

    public function test_fixed_costs_and_expenses_pnl_statement(): void
    {
        $today = Carbon::today()->toDateString();

        $response = $this->post('/admin/expenses', [
            'title' => 'ค่าเช่าที่หน้าร้านรายวัน',
            'type' => 'fixed_cost',
            'category' => 'rent',
            'amount' => 300.00,
            'expense_date' => $today,
            'notes' => 'จ่ายสด',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('expenses', [
            'title' => 'ค่าเช่าที่หน้าร้านรายวัน',
            'type' => 'fixed_cost',
            'amount' => 300.00,
        ]);

        $pageResp = $this->get("/admin/expenses?date={$today}");
        $pageResp->assertStatus(200);
        $pageResp->assertSee('ค่าเช่าที่หน้าร้านรายวัน');
    }

    public function test_inventory_matrix_and_daily_reconcile(): void
    {
        $today = Carbon::today()->toDateString();
        $product = Product::firstOrFail();

        // Adjust add 5 sticks
        $adjustResp = $this->post('/admin/inventory/adjust', [
            'product_id' => $product->id,
            'date' => $today,
            'action' => 'add',
            'quantity' => 5,
            'notes' => 'เสียบเพิ่ม',
        ]);
        $adjustResp->assertRedirect();

        // Reconcile actual count
        $reconcileResp = $this->post('/admin/inventory/reconcile', [
            'date' => $today,
            'actual_counts' => [
                $product->id => 20,
            ]
        ]);
        $reconcileResp->assertRedirect();

        $record = InventoryDaily::where('product_id', $product->id)->whereDate('record_date', $today)->first();
        $this->assertNotNull($record);
        $this->assertEquals(20, $record->actual_closing_sticks);
    }

    public function test_receipt_image_upload_and_ocr_scanning(): void
    {
        // 1. Test image upload endpoint
        $dummyBase64 = 'data:image/jpeg;base64,' . base64_encode('fake-jpg-binary-content');
        $uploadResp = $this->postJson('/admin/receipts/upload-image', [
            'image_base64' => $dummyBase64,
        ]);

        $uploadResp->assertStatus(200);
        $uploadResp->assertJson(['success' => true]);
        $imageUrl = $uploadResp->json('image_url');
        $imagePath = $uploadResp->json('image_path');
        $this->assertNotEmpty($imageUrl);
        $this->assertNotEmpty($imagePath);

        // 2. Test smart OCR scanner with genuine receipt text (No fake mock items)
        $realOcrText = "สยามแม็คโคร สาขาบางบัวทอง\n"
            . "TAX INV: MK-20261106-9912\n"
            . "วันที่ 06/11/2026 14:30\n"
            . "1. ไส้กรอกไก่รมควัน 1 แพ็ก 65.00\n"
            . "2. เอ็นข้อไก่สด 2 กิโลกรัม 180.00\n"
            . "ยอดรวม TOTAL 245.00";

        $ocrResp = $this->postJson('/admin/receipts/scan-ocr', [
            'image_path' => $imagePath,
            'image_base64' => $dummyBase64,
            'raw_text' => $realOcrText,
        ]);

        $ocrResp->assertStatus(200);
        $ocrResp->assertJson(['success' => true]);
        $this->assertEquals('MK-20261106-9912', $ocrResp->json('receipt_no'));
        $this->assertStringContainsString('แม็คโคร', $ocrResp->json('vendor_name'));
        $this->assertNotEmpty($ocrResp->json('items'));
        $this->assertGreaterThan(0, $ocrResp->json('total_amount'));

        // 3. Test that empty/unreadable image does NOT generate random fake items
        $blankResp = $this->postJson('/admin/receipts/scan-ocr', [
            'image_path' => $imagePath,
            'image_base64' => $dummyBase64,
            'raw_text' => '',
        ]);
        $blankResp->assertStatus(200);
        $blankResp->assertJson(['success' => false]);
        $this->assertEmpty($blankResp->json('items'));

        // 4. Test save Gemini API Key endpoint
        $saveKeyResp = $this->postJson('/admin/receipts/save-gemini-key', [
            'gemini_api_key' => 'TEST_API_KEY_12345',
        ]);
        $saveKeyResp->assertStatus(200);
        $saveKeyResp->assertJson(['success' => true]);

        // Clean up dummy uploaded file
        if (file_exists(public_path($imagePath))) {
            unlink(public_path($imagePath));
        }
    }
}

