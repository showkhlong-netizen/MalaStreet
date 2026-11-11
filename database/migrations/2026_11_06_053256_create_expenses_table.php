<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->string('title'); // e.g. ค่าเช่าล็อคตลาด, ค่าไฟ, ค่าแก๊สกระป๋อง, ค่าแรงพนักงาน
            $table->enum('type', ['fixed_cost', 'variable_expense', 'income', 'daily_operating'])->default('fixed_cost');
            $table->string('category')->default('other'); // rent, labor, utilities, supplies, ingredients, other
            $table->decimal('amount', 10, 2);
            $table->date('expense_date');
            $table->string('receipt_image')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};

