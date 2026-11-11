<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->integer('quantity_sticks')->default(1); // จำนวนไม้
            $table->decimal('unit_price', 10, 2); // ราคาต่อไม้
            $table->decimal('subtotal', 10, 2);
            $table->enum('cooking_type', ['grill', 'soup'])->default('grill'); // ย่าง vs ต้มน้ำซุป
            $table->string('grill_style')->nullable(); // 'brush' (ทา) หรือ 'dip' (จุ่ม)
            $table->string('soup_style')->nullable(); // 'mala_rich' (หม่าล่าน้ำข้น), 'tomyum_rich' (ต้มยำน้ำข้น), 'black_soup' (น้ำดำ)
            $table->string('spiciness_level')->default('medium'); // 'none', 'mild' (เผ็ดน้อย), 'medium' (เผ็ดกลาง), 'hot' (เผ็ดมาก)
            $table->text('special_instructions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

