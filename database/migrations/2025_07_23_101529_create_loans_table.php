<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // The name of the borrower
            $table->string('borrower_name');
            // Optional phone number of borrower
            $table->string('borrower_phone')->nullable();

            $table->decimal('amount', 12, 2);

            // Date the loan was given
            $table->date('date_given');
            // Expected date of return (optional)
            $table->date('expected_return_date')->nullable();

            // Status of the loan: borrowed (given), returned, or lost
            $table->enum('status', ['borrowed', 'returned', 'lost'])->default('borrowed');

            // Amount returned (for partial or full repayment)
            $table->decimal('returned_amount', 12, 2)->default(0);

            // Channel used to give the loan (bank, momo, cash, other)
            $table->string('channel');

            // Account from which the loan money was given
            $table->foreignId('account_id')->constrained()->onDelete('cascade');

            // Notes or description for the loan
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
