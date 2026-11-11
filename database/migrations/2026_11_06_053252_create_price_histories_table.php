<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('raw_material_id')->constrained('raw_materials')->cascadeOnDelete();
            $table->foreignId('receipt_id')->nullable()->constrained('receipts')->nullOnDelete();
            $table->string('vendor_name'); // Makro, Lotus, ตลาดสดเขียงหมู
            $table->string('invoice_number')->nullable();
            $table->decimal('purchase_unit_price', 10, 2); // เช่น 65.00 บาท
            $table->string('purchase_unit')->default('แพ็ก');
            $table->decimal('yield_sticks_per_unit', 8, 2)->default(10.00);
            $table->decimal('cost_per_stick', 10, 2); // 6.50 บาท
            $table->decimal('previous_cost_price', 10, 2)->nullable(); // เช่น เมื่อวาน 60.00
            $table->decimal('price_change_amount', 10, 2)->nullable(); // +5.00
            $table->decimal('price_change_percent', 8, 2)->nullable(); // +8.33%
            $table->date('recorded_at'); // วันที่บันทึก
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_histories');
    }
};

