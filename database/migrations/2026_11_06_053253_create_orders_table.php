<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_no')->unique(); // e.g. MS-20261106-001
            $table->string('queue_number'); // e.g. A01, A02, B01
            $table->enum('order_type', ['pos', 'online'])->default('pos'); // หน้าร้าน vs สั่งออนไลน์มารับเอง
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('pickup_time')->nullable(); // เช่น 'ทันที (~15 นาที)' หรือ '18:30 น.'
            $table->enum('status', ['pending', 'confirmed', 'cooking', 'ready', 'completed', 'cancelled'])->default('pending');
            $table->enum('payment_method', ['cash', 'promptpay'])->default('promptpay');
            $table->enum('payment_status', ['unpaid', 'paid', 'refunded'])->default('unpaid');
            $table->decimal('subtotal', 10, 2);
            $table->decimal('soup_extra', 10, 2)->default(0.00); // ค่าน้ำซุปพิเศษ (ถ้ามี)
            $table->decimal('discount', 10, 2)->default(0.00);
            $table->decimal('total_amount', 10, 2);
            $table->decimal('cash_received', 10, 2)->nullable();
            $table->decimal('change_amount', 10, 2)->nullable();
            $table->string('slip_image')->nullable(); // สลิปโอนเงิน PromptPay ที่บีบอัดแล้ว
            $table->text('notes')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

