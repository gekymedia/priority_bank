<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Link to an expense category; nullable for uncategorised expenses.
            $table->foreignId('expense_category_id')->nullable()->constrained()->nullOnDelete();

            // Account from which this expense was paid
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 12, 2);
            $table->date('date');

            // Channel indicates how the payment was made (bank, momo, cash, other)
            $table->string('channel');

            // Notes or description of the expense
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};
