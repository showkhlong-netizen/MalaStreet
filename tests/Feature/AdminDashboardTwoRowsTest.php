<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Expense;
use App\Models\InventoryDaily;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminDashboardTwoRowsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_two_rows_of_financial_and_inventory_kpis(): void
    {
        $admin = User::create([
            'name' => 'Admin Dashboard',
            'email' => 'admin-dash@malastreet.com',
            'password' => Hash::make('password123'),
        ]);

        $cat = Category::create(['name' => 'น้ำจิ้ม', 'slug' => 'sauce', 'sort_order' => 1]);
        $sauce = Product::create([
            'category_id' => $cat->id,
            'name' => 'น้ำจิ้มหม่าล่าสูตรเด็ด',
            'price_per_stick' => 15,
            'is_available' => true,
            'stock_sticks' => 25,
        ]);

        $cashOrder = Order::create([
            'order_no' => 'MS-POS-CASH-01',
            'queue_number' => 'A01',
            'order_type' => 'pos',
            'status' => 'completed',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 100,
            'total_amount' => 100,
        ]);

        $onlineOrder = Order::create([
            'order_no' => 'MS-ONL-01',
            'queue_number' => 'G01',
            'order_type' => 'online',
            'status' => 'confirmed',
            'payment_method' => 'promptpay',
            'payment_status' => 'paid',
            'subtotal' => 45,
            'total_amount' => 45,
        ]);

        OrderItem::create([
            'order_id' => $onlineOrder->id,
            'product_id' => $sauce->id,
            'product_name' => $sauce->name,
            'quantity_sticks' => 3,
            'unit_price' => 15,
            'subtotal' => 45,
            'cooking_type' => 'grill',
            'spiciness_level' => 'medium',
        ]);

        $withdrawOrder = Order::create([
            'order_no' => 'MS-STAFF-01',
            'queue_number' => 'S01',
            'order_type' => 'pos',
            'customer_name' => 'พนักงานเบิกกิน',
            'notes' => 'เบิกมากิน',
            'status' => 'completed',
            'payment_method' => 'cash',
            'payment_status' => 'unpaid',
            'subtotal' => 0,
            'total_amount' => 0,
        ]);

        OrderItem::create([
            'order_id' => $withdrawOrder->id,
            'product_id' => $sauce->id,
            'product_name' => 'ไส้กรอกแดง',
            'quantity_sticks' => 2,
            'unit_price' => 10,
            'subtotal' => 0,
            'cooking_type' => 'grill',
            'spiciness_level' => 'mild',
        ]);

        InventoryDaily::create([
            'product_id' => $sauce->id,
            'record_date' => Carbon::today(),
            'opening_sticks' => 40,
            'added_sticks' => 10,
            'sold_sticks' => 3,
            'waste_sticks' => 5,
            'expected_closing_sticks' => 42,
        ]);

        Expense::create([
            'title' => 'ค่าเช่าที่',
            'type' => 'fixed_cost',
            'amount' => 300,
            'expense_date' => Carbon::today(),
        ]);

        $res = $this->actingAs($admin)->get(route('admin.dashboard'));
        $res->assertStatus(200);

        // Row 1 labels
        $res->assertSee('รวมเงินสด');
        $res->assertSee('รวมเงินโอน');
        $res->assertSee('สั่งออนไลน์');
        $res->assertSee('รวมเงินผ่านแอพ');
        $res->assertSee('ยอดขายรวม');
        $res->assertSee('รายจ่ายรวม');

        // Row 2 labels + sauce money + counts
        $res->assertSee('ขายไปแล้วกี่ไม้');
        $res->assertSee('สต๊อกไม้คงเหลือ');
        $res->assertSee('ไม้เสียทิ้ง');
        $res->assertSee('ไม้ที่เหลือจากเมื่อวาน');
        $res->assertSee('น้ำจิ้มขายออก');
        $res->assertSee('ยอดเงิน: ฿45');
        $res->assertSee('รายการเบิก');
    }
}
