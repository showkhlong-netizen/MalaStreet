<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\DataResetSnapshot;
use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MalaStreetAccessAndResetKitTest extends TestCase
{
    use RefreshDatabase;

    public function test_kit_tables_permissions_and_30_day_data_reset_restore_work_end_to_end(): void
    {
        $admin = User::create([
            'name' => 'Munib Salaemae',
            'email' => 'salaemaemunib@gmail.com',
            'password' => Hash::make('1122'),
        ]);

        // 1. Roles & Branches seeded by migration
        $rolesCount = DB::table('roles')->count();
        $this->assertSame(6, $rolesCount);
        $this->assertGreaterThanOrEqual(3, Branch::count());
        $this->assertSame('admin', $admin->getRoleName());
        $this->assertTrue($admin->hasPermission('access.reset.delete'));

        // 2. Seed a POS order and an expense in MalaStreet
        $order = Order::create([
            'order_no' => 'MS-20261114-001',
            'queue_number' => 'A01',
            'order_type' => 'pos',
            'customer_name' => 'ลูกค้าทดสอบ',
            'status' => 'completed',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'subtotal' => 150,
            'soup_extra' => 0,
            'discount' => 0,
            'total_amount' => 150,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_name' => 'สามชั้นหม่าล่า',
            'unit_price' => 15,
            'quantity_sticks' => 10,
            'subtotal' => 150,
        ]);

        Expense::create([
            'title' => 'ค่าวัตถุดิบสด',
            'type' => 'daily_operating',
            'category' => 'ingredients',
            'amount' => 500,
            'expense_date' => now()->toDateString(),
        ]);

        // 3. Stats endpoint returns live counts
        $statsRes = $this->actingAs($admin)->getJson('/access/reset/stats?branch_id=ALL');
        $statsRes->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, $statsRes->json('stats.POS_ORDERS.count'));
        $this->assertEquals(150.0, $statsRes->json('stats.POS_ORDERS.total_amount'));
        $this->assertSame(1, $statsRes->json('stats.DAILY_EXPENSES.count'));

        // 4. Execute Reset for POS_ORDERS (backed up for 30 days)
        $execRes = $this->actingAs($admin)->postJson('/access/reset/execute', [
            'scope_key' => 'POS_ORDERS',
            'date_filter' => 'ALL',
            'branch_id' => 'ALL',
            'confirm_mode' => 'code',
            'confirm_value' => 'RESET',
            'reason' => 'ล้างบิลทดสอบ',
        ]);
        $execRes->assertOk()->assertJsonPath('success', true);
        $snapshotId = $execRes->json('snapshot.id');
        $this->assertNotNull($snapshotId);
        $this->assertSame(0, Order::count());
        $this->assertSame(0, OrderItem::count());

        // 5. Restore from 30-day snapshot
        $restoreRes = $this->actingAs($admin)->postJson("/access/reset/{$snapshotId}/restore", [
            'confirm_mode' => 'password',
            'confirm_value' => '1122',
        ]);
        $restoreRes->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, Order::count());
        $this->assertSame(1, OrderItem::count());
        $this->assertTrue((bool) Order::first()->is_restored);

        // 6. Purge snapshot from backup bin without deleting live orders
        $purgeRes = $this->actingAs($admin)->deleteJson("/access/reset/{$snapshotId}/purge", [
            'confirm_mode' => 'code',
            'confirm_value' => 'RESET',
        ]);
        $purgeRes->assertOk()->assertJsonPath('success', true);
        $this->assertSame(0, DataResetSnapshot::count());
        $this->assertSame(1, Order::count());

        // 7. Save per-user permissions & branch binding
        $staff = User::create([
            'name' => 'พนักงานสาขายะลา',
            'email' => 'yala.staff@malastreet.com',
            'password' => Hash::make('1234'),
        ]);
        $branchId = Branch::first()->id;

        $permRes = $this->actingAs($admin)->postJson("/access/users/{$staff->id}/permissions", [
            'permissions' => ['pos.orders.view', 'pos.orders.create', 'kitchen.queue.view'],
            'branch_ids' => [$branchId],
        ]);
        $permRes->assertOk()->assertJsonPath('success', true);
        $this->assertSame([$branchId], $permRes->json('branch_ids'));

        // 8. Verify /admin/access?tab=reset renders the Reset Hub UI cleanly
        $page = $this->actingAs($admin)->get('/admin/access?tab=reset');
        $page->assertOk();
        $page->assertSee('ระบบรีเซ็ทข้อมูลร้านหม่าล่าสตรีท', false);
        $page->assertSee('ถังสำรองข้อมูล 30 วัน', false);
    }
}
