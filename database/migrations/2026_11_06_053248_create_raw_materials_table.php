<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('raw_materials', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g. ลูกชิ้นปลา, ไส้กรอกไก่, เอ็นไก่, สันคอหมู
            $table->string('sku')->nullable();
            $table->string('default_vendor')->nullable(); // e.g. Makro, Lotus, ตลาดสด
            $table->string('purchase_unit')->default('แพ็ก'); // แพ็ก, กิโลกรัม, ถุง
            $table->decimal('yield_sticks_per_unit', 8, 2)->default(10.00); // แปลงออกมาเป็นชิ้นหรือไม้ เช่น ลูกชิ้น 1 แพ็ก ได้ 10 ไม้
            $table->decimal('latest_cost_price', 10, 2)->default(0.00); // ราคาซื้อล่าสุดต่อหน่วย
            $table->decimal('cost_per_stick', 10, 2)->default(0.00); // ต้นทุนต่อไม้ (latest_cost_price / yield_sticks_per_unit)
            $table->integer('current_stock_sticks')->default(0); // จำนวนไม้คงเหลือปัจจุบัน
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('raw_materials');
    }
};

