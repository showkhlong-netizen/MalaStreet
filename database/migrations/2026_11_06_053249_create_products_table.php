<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('raw_material_id')->nullable()->constrained('raw_materials')->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_per_stick', 10, 2); // ราคาขายต่อไม้
            $table->string('image_url')->nullable();
            $table->integer('stock_sticks')->default(0); // จำนวนไม้พร้อมขาย
            $table->boolean('is_available')->default(true);
            $table->boolean('supports_grill')->default(true); // รองรับแบบย่าง
            $table->boolean('supports_soup')->default(true); // รองรับแบบต้มน้ำซุป
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

