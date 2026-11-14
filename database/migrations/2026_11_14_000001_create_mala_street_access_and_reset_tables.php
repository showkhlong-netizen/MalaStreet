<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. ตารางตำแหน่งงาน (Roles) สำหรับร้านหม่าล่าสตรีท
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('name', 50)->unique();
                $table->string('label', 120);
                $table->text('description')->nullable();
                $table->timestamps();
            });

            $now = now();
            DB::table('roles')->insert([
                ['name' => 'admin',   'label' => 'เจ้าของร้าน (Super Admin)', 'description' => 'สิทธิ์สูงสุดทุกสาขาและทุกเมนู', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'manager', 'label' => 'ผู้จัดการร้าน / สาขา',     'description' => 'ดูแลภาพรวมสาขา อนุมัติยกเลิกบิล และสต็อก', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'cashier', 'label' => 'แคชเชียร์ / พนักงานขาย POS', 'description' => 'เปิดโต๊ะ คิดเงิน รับชำระ และบันทึกรายจ่ายหน้าร้าน', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'kitchen', 'label' => 'ฝ่ายครัว / ปิ้งย่าง-ต้มหม่าล่า', 'description' => 'ดูจอคิวออเดอร์ครัว (KDS) และอัปเดตสถานะอาหาร', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'stock',   'label' => 'ฝ่ายสต็อกวัตถุดิบ',         'description' => 'รับเข้าวัตถุดิบ นับไม้/ชั่งกรัม และตัดของเสีย', 'created_at' => $now, 'updated_at' => $now],
                ['name' => 'finance', 'label' => 'ฝ่ายบัญชีและการเงิน',       'description' => 'ตรวจสอบรายรับ-จ่าย ลิ้นชักเงินสด และกระเป๋าหลัก', 'created_at' => $now, 'updated_at' => $now],
            ]);
        }

        // 2. ตารางสาขา (Branches) และตารางเชื่อมพนักงานกับสาขา (branch_user)
        if (!Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('code', 30)->unique();
                $table->string('name', 120);
                $table->string('phone', 50)->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_main')->default(false);
                $table->string('status', 20)->default('ACTIVE'); // ACTIVE, INACTIVE
                $table->timestamps();
            });

            $now = now();
            DB::table('branches')->insert([
                [
                    'code' => 'MALA-HQ',
                    'name' => 'สาขาหลัก (หม่าล่าสตรีท)',
                    'is_main' => true,
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'MALA-YLA',
                    'name' => 'สาขายะลา',
                    'is_main' => false,
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'code' => 'MALA-PTN',
                    'name' => 'สาขาปัตตานี',
                    'is_main' => false,
                    'status' => 'ACTIVE',
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ]);
        }

        if (!Schema::hasTable('branch_user')) {
            Schema::create('branch_user', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('branch_id')->index();
                $table->unsignedBigInteger('user_id')->index();
                $table->timestamps();
                $table->unique(['branch_id', 'user_id']);
            });
        }

        // 3. ตารางโปรไฟล์พนักงาน (staff_profiles)
        if (!Schema::hasTable('staff_profiles')) {
            Schema::create('staff_profiles', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->unique();
                $table->unsignedBigInteger('role_id')->nullable()->index();
                $table->string('employee_code', 40)->nullable();
                $table->string('first_name', 120)->nullable();
                $table->string('last_name', 120)->nullable();
                $table->string('phone', 40)->nullable();
                $table->boolean('is_active')->default(true)->index();
                $table->boolean('has_custom_permissions')->default(false);
                $table->timestamp('permissions_updated_at')->nullable();
                $table->timestamps();
            });
        }

        // 4. ตารางเก็บสิทธิ์รายบุคคล (user_permission_overrides)
        if (!Schema::hasTable('user_permission_overrides')) {
            Schema::create('user_permission_overrides', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('permission_key', 120)->index();
                $table->boolean('is_allowed')->default(true);
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'permission_key']);
            });
        }

        // 5. ตารางคำขอสมัครใช้งานพนักงานใหม่ (access_requests)
        if (!Schema::hasTable('access_requests')) {
            Schema::create('access_requests', function (Blueprint $table) {
                $table->id();
                $table->string('name', 150);
                $table->string('email', 150)->index();
                $table->string('phone', 50)->nullable();
                $table->string('password_plain', 255)->nullable();
                $table->unsignedBigInteger('role_id')->nullable();
                $table->string('status', 30)->default('PENDING')->index(); // PENDING, APPROVED, REJECTED
                $table->unsignedBigInteger('reviewed_by')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        // 6. ตารางถังสำรองข้อมูล 30 วัน (data_reset_snapshots)
        if (!Schema::hasTable('data_reset_snapshots')) {
            Schema::create('data_reset_snapshots', function (Blueprint $table) {
                $table->id();
                $table->string('batch_number', 40)->unique();
                $table->string('scope_key', 60)->index();
                $table->string('scope_label', 150);
                $table->unsignedBigInteger('branch_id')->nullable()->index();
                $table->string('date_filter', 30)->default('ALL');
                $table->unsignedInteger('records_count')->default(0);
                $table->decimal('total_amount', 16, 2)->default(0);
                $table->text('summary_notes')->nullable();
                $table->longText('payload'); // JSON เก็บข้อมูลดิบทั้งหมดเพื่อกู้คืนภายใน 30 วัน
                $table->unsignedBigInteger('performed_by')->nullable()->index();
                $table->string('performer_name', 120)->nullable();
                $table->string('status', 30)->default('BACKED_UP')->index(); // BACKED_UP, RESTORED, EXPIRED
                $table->timestamp('restored_at')->nullable();
                $table->unsignedBigInteger('restored_by')->nullable();
                $table->string('restorer_name', 120)->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // 7. เพิ่มคอลัมน์ branch_id, is_restored และ restored_at บนตารางธุรกรรมของหม่าล่าสตรีท
        $transactionalTables = [
            'orders',
            'expenses',
            'receipts',
            'inventory_dailies',
            'customer_rewards',
            'customer_reviews',
            'reward_redemptions',
            'chat_messages',
            'incomes',
            'treasury_transactions',
            'stock_movements',
            'customers',
        ];

        foreach ($transactionalTables as $tableName) {
            if (Schema::hasTable($tableName)) {
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (!Schema::hasColumn($tableName, 'branch_id')) {
                        $table->unsignedBigInteger('branch_id')->nullable()->index();
                    }
                    if (!Schema::hasColumn($tableName, 'is_restored')) {
                        $table->boolean('is_restored')->default(false)->index();
                    }
                    if (!Schema::hasColumn($tableName, 'restored_at')) {
                        $table->timestamp('restored_at')->nullable();
                    }
                });
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('data_reset_snapshots');
        Schema::dropIfExists('access_requests');
        Schema::dropIfExists('user_permission_overrides');
        Schema::dropIfExists('staff_profiles');
        Schema::dropIfExists('branch_user');
    }
};
