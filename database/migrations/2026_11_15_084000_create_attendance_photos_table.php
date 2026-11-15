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
        Schema::create('attendance_photos', function (Blueprint $table) {
            $table->id();
            $table->string('filename')->unique();
            $table->string('employee_id', 64)->nullable()->index();
            $table->string('action_type', 20)->default('check_in');
            $table->string('mime_type', 50)->default('image/jpeg');
            $table->longText('image_base64');
            $table->timestamp('expires_at')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_photos');
    }
};
