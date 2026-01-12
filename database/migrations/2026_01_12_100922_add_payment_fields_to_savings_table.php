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
        Schema::table('savings', function (Blueprint $table) {
            $table->enum('payment_method', ['direct', 'paystack', 'hubtel'])->default('direct')->after('reference');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('approved')->after('payment_method');
            $table->string('transaction_reference')->nullable()->after('approval_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('savings', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'approval_status', 'transaction_reference']);
        });
    }
};
