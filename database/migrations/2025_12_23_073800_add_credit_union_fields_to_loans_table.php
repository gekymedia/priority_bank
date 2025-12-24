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
        Schema::table('loans', function (Blueprint $table) {
            // Add credit union specific fields
            $table->foreignId('loan_request_id')->nullable()->after('id')
                ->constrained('loan_requests')->nullOnDelete();
            $table->foreignId('interest_rate_id')->nullable()->after('amount')
                ->constrained('interest_rates')->nullOnDelete();
            $table->decimal('interest_rate_applied', 5, 2)->nullable()->after('interest_rate_id');
            $table->decimal('total_amount_with_interest', 12, 2)->nullable()->after('interest_rate_applied');
            $table->decimal('remaining_balance', 12, 2)->default(0)->after('returned_amount');
            $table->date('disbursement_date')->nullable()->after('date_given');
            $table->enum('loan_type', ['personal', 'business', 'emergency'])->default('personal')->after('channel');
            $table->boolean('is_credit_union_loan')->default(false)->after('loan_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            $table->dropForeign(['loan_request_id']);
            $table->dropForeign(['interest_rate_id']);
            $table->dropColumn([
                'loan_request_id',
                'interest_rate_id',
                'interest_rate_applied',
                'total_amount_with_interest',
                'remaining_balance',
                'disbursement_date',
                'loan_type',
                'is_credit_union_loan'
            ]);
        });
    }
};
