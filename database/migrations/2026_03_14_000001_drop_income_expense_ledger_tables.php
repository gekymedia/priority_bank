<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop Income/Expense ledger tables. CEO ledger is now done directly on the CEO account (transactions).
     */
    public function up(): void
    {
        // fund_transfers references expenses - drop FK and column first
        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->dropForeign(['expense_id']);
            $table->dropColumn('expense_id');
        });

        // budgets references expense_categories - drop FK and column
        Schema::table('budgets', function (Blueprint $table) {
            $table->dropForeign(['expense_category_id']);
            $table->dropColumn('expense_category_id');
        });

        Schema::dropIfExists('expenses');
        Schema::dropIfExists('incomes');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('income_categories');
    }

    public function down(): void
    {
        // Recreate in reverse order - categories first, then incomes/expenses, then alter fund_transfers/budgets
        Schema::create('income_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('income_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('channel');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('account_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->date('date');
            $table->string('channel');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('fund_transfers', function (Blueprint $table) {
            $table->unsignedBigInteger('expense_id')->nullable()->after('notes');
            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('set null');
        });

        Schema::table('budgets', function (Blueprint $table) {
            $table->foreignId('expense_category_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }
};
