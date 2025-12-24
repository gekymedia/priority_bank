<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Each income belongs to a category (source). Nullable to allow unclassified incomes.
            $table->foreignId('income_category_id')->nullable()->constrained()->nullOnDelete();

            // Account into which this income was received
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            $table->decimal('amount', 12, 2);
            $table->date('date');

            // Channel indicates where the money came from (bank, momo, cash, other)
            $table->string('channel');

            // Notes or description about the income
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incomes');
    }
};
