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
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('from_fund_type'); // 'api_1', 'api_2', 'savings'
            $table->unsignedBigInteger('from_system_id')->nullable(); // For API funds
            $table->string('to_fund_type'); // 'savings', 'api_1', 'api_2'
            $table->unsignedBigInteger('to_system_id')->nullable(); // For API funds
            $table->decimal('amount', 15, 2);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('expense_id')->nullable(); // Link to expense record
            $table->unsignedBigInteger('created_by'); // Admin user ID
            $table->timestamps();

            $table->foreign('expense_id')->references('id')->on('expenses')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
