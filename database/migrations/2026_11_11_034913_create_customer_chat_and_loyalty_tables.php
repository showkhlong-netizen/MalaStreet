<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. ตารางข้อความแชทลูกค้าระหว่างร้านหม่าล่าสตรีทกับลูกค้า
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->nullable()->index();
            $table->unsignedBigInteger('order_id')->nullable()->index();
            $table->string('order_no')->nullable()->index();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable()->index();
            $table->enum('sender', ['customer', 'shop'])->default('customer');
            $table->text('message')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

        // 2. ตารางแต้มสะสมลูกค้า (1 ซื้อ = 1 แต้ม, 5 แต้ม = ฟรี 1 ไม้)
        Schema::create('customer_rewards', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone')->unique();
            $table->string('customer_name')->nullable();
            $table->integer('points')->default(0);
            $table->integer('total_stamps')->default(0);
            $table->integer('free_skewers_redeemed')->default(0);
            $table->timestamps();
        });

        // 3. ตารางรีวิวอาหารจากลูกค้า (เขียนรีวิวรับ +1 แต้ม)
        Schema::create('customer_reviews', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone')->nullable()->index();
            $table->string('customer_name')->default('ลูกค้าผู้มีอุปการคุณ');
            $table->string('order_no')->nullable()->index();
            $table->integer('rating')->default(5);
            $table->string('favorite_dishes')->nullable();
            $table->text('comment')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->integer('bonus_points')->default(1);
            $table->timestamps();
        });

        // 4. ตารางประวัติการแลกสิทธิ์/คูปองฟรี 1 ไม้ (Redemptions)
        Schema::create('reward_redemptions', function (Blueprint $table) {
            $table->id();
            $table->string('customer_phone')->index();
            $table->string('voucher_code')->unique();
            $table->string('reward_title')->default('ฟรีหม่าล่า 1 ไม้');
            $table->integer('points_spent')->default(5);
            $table->enum('status', ['active', 'used', 'expired'])->default('active');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reward_redemptions');
        Schema::dropIfExists('customer_reviews');
        Schema::dropIfExists('customer_rewards');
        Schema::dropIfExists('chat_messages');
    }
};
