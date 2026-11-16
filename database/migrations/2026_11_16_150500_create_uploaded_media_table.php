<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('uploaded_media')) {
            Schema::create('uploaded_media', function (Blueprint $table) {
                $table->id();
                $table->string('media_key', 255)->unique();
                $table->string('mime_type', 100)->default('image/jpeg');
                $table->longText('data_base64');
                $table->unsignedInteger('size_bytes')->default(0);
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_media');
    }
};
