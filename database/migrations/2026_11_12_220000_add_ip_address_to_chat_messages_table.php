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
        if (Schema::hasTable('chat_messages') && !Schema::hasColumn('chat_messages', 'ip_address')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->string('ip_address', 50)->nullable()->after('customer_phone')->index();
            });
        }

        if (Schema::hasTable('orders') && !Schema::hasColumn('orders', 'ip_address')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->string('ip_address', 50)->nullable()->after('customer_phone');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('chat_messages') && Schema::hasColumn('chat_messages', 'ip_address')) {
            Schema::table('chat_messages', function (Blueprint $table) {
                $table->dropColumn('ip_address');
            });
        }

        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'ip_address')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('ip_address');
            });
        }
    }
};
