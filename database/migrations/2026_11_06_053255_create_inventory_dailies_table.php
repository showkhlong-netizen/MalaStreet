<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_dailies', function (Blueprint $table) {
            $table->id();
            $table->date('record_date'); // วันที่บันทึก
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('opening_sticks')->default(0); // ยอดไม้คงเหลือยกมาจากเมื่อวาน เช่น เอ็นไก่ เหลือ 3 ไม้
            $table->integer('added_sticks')->default(0); // ยอดไม้ที่เสียบหรือซื้อเพิ่มวันนี้ เช่น +10 ไม้
            $table->integer('sold_sticks')->default(0); // ยอดไม้ที่ขายออกไปในวันนี้ (POS + Online)
            $table->integer('waste_sticks')->default(0); // ไม้ที่เสีย/ทำตกหล่น
            $table->integer('expected_closing_sticks')->default(0); // ยอดไม้คงเหลือตามระบบ (opening + added - sold - waste)
            $table->integer('actual_closing_sticks')->nullable(); // ยอดไม้ที่ตรวจนับจริงหน้าร้านสิ้นวัน
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['record_date', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_dailies');
    }
};

