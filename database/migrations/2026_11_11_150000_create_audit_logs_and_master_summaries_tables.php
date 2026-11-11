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
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->string('user_id', 64)->nullable()->index();
                $table->string('user_name', 150)->default('ระบบอัตโนมัติ');
                $table->string('user_role', 80)->default('Admin');
                $table->string('branch', 100)->default('สาขาหลัก');
                $table->string('module', 80)->index(); // pos, kitchen, receipts, price_tracker, inventory, expenses, employees, access, online
                $table->string('action', 60)->index(); // create, update, delete, status, reconcile, clock_in, login, etc.
                $table->string('action_label', 150);
                $table->string('target_name', 255)->nullable();
                $table->longText('details')->nullable(); // JSON details
                $table->string('ip_address', 64)->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('master_summaries')) {
            Schema::create('master_summaries', function (Blueprint $table) {
                $table->id();
                $table->string('doc_key', 100)->unique(); // e.g. global_sync_state, pos_master, price_tracker_master, inventory_master, dashboard_master
                $table->unsignedBigInteger('version')->default(1);
                $table->longText('payload'); // Materialized JSON document
                $table->string('updated_by', 150)->nullable();
                $table->unsignedBigInteger('updated_at_ms')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('master_summaries');
    }
};
