<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->id();
            $table->string('receipt_no')->nullable(); // เลขที่ใบเสร็จ / ใบกำกับภาษี / Order ID
            $table->string('vendor_name'); // Makro, Lotus, ตลาดสด, ซัพพลายเออร์
            $table->date('purchase_date'); // วันที่ซื้อ
            $table->decimal('total_amount', 10, 2)->default(0.00); // ยอดรวมทั้งบิล
            $table->string('image_path')->nullable(); // ภาพสแกนที่บีบอัดแล้ว
            $table->text('raw_ocr_text')->nullable(); // ข้อความดิบจาก OCR
            $table->json('parsed_data')->nullable(); // ข้อมูลจัดโครงสร้าง
            $table->string('status')->default('processed'); // processed, verified
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};

