<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('quantity', 8, 2)->default(1.00); // เช่น 2 แพ็ก, 3.5 กก.
            $table->string('unit')->default('แพ็ก');
            $table->decimal('unit_price', 10, 2); // ราคาต่อแพ็ก
            $table->decimal('total_price', 10, 2); // ราคารวม
            $table->decimal('yield_sticks_per_unit', 8, 2)->default(10.00); // 1 หน่วยได้กี่ไม้
            $table->integer('total_yield_sticks')->default(10); // จำนวนไม้ที่ได้ทั้งหมด (quantity * yield)
            $table->decimal('cost_per_stick', 10, 2)->default(0.00); // ต้นทุนต่อไม้
            $table->boolean('is_added_to_inventory')->default(false); // นำเข้าสต็อกไม้แล้วหรือยัง
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_items');
    }
};

