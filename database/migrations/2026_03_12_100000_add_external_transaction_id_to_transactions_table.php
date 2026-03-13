<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds external_transaction_id for 2-way reconciliation with entity projects
     * (e.g. GEKYMEDIA, SchoolsGH, CUG). Projects store the same id on their
     * income/expense records so both sides can confirm a match.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('external_transaction_id', 64)->nullable()->after('external_system_id');
            $table->index('external_transaction_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropIndex(['external_transaction_id']);
            $table->dropColumn('external_transaction_id');
        });
    }
};
