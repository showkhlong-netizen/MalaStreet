<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                if (!Schema::hasColumn('orders', 'kitchen_step')) {
                    $table->unsignedTinyInteger('kitchen_step')->nullable()->after('status');
                }
                if (!Schema::hasColumn('orders', 'cooking_started_at')) {
                    $table->timestamp('cooking_started_at')->nullable()->after('kitchen_step');
                }
                if (!Schema::hasColumn('orders', 'paid_cash_amount')) {
                    $table->decimal('paid_cash_amount', 10, 2)->nullable()->after('change_amount');
                }
                if (!Schema::hasColumn('orders', 'paid_transfer_amount')) {
                    $table->decimal('paid_transfer_amount', 10, 2)->nullable()->after('paid_cash_amount');
                }
                if (!Schema::hasColumn('orders', 'delivery_note')) {
                    $table->text('delivery_note')->nullable()->after('notes');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('orders')) {
            Schema::table('orders', function (Blueprint $table) {
                $cols = array_filter([
                    Schema::hasColumn('orders', 'kitchen_step') ? 'kitchen_step' : null,
                    Schema::hasColumn('orders', 'cooking_started_at') ? 'cooking_started_at' : null,
                    Schema::hasColumn('orders', 'paid_cash_amount') ? 'paid_cash_amount' : null,
                    Schema::hasColumn('orders', 'paid_transfer_amount') ? 'paid_transfer_amount' : null,
                    Schema::hasColumn('orders', 'delivery_note') ? 'delivery_note' : null,
                ]);
                if (!empty($cols)) {
                    $table->dropColumn(array_values($cols));
                }
            });
        }
    }
};
