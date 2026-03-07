<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Links transactions created from approved deposits to the source Saving
     * so the same notes from the Savings page can be shown in transaction "view more".
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('saving_id')->nullable()->after('external_system_id')
                ->constrained('savings')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['saving_id']);
            $table->dropColumn('saving_id');
        });
    }
};
