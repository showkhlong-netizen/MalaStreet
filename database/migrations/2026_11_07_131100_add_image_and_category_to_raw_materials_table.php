<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            if (!Schema::hasColumn('raw_materials', 'image_url')) {
                $table->string('image_url')->nullable()->after('name');
            }
            if (!Schema::hasColumn('raw_materials', 'category_slug')) {
                $table->string('category_slug')->nullable()->after('image_url');
            }
        });
    }

    public function down(): void
    {
        Schema::table('raw_materials', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('raw_materials', 'image_url')) {
                $cols[] = 'image_url';
            }
            if (Schema::hasColumn('raw_materials', 'category_slug')) {
                $cols[] = 'category_slug';
            }
            if (!empty($cols)) {
                $table->dropColumn($cols);
            }
        });
    }
};

