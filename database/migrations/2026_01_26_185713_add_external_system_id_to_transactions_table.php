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
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreignId('external_system_id')->nullable()->after('user_id')
                ->constrained('systems_registry')
                ->onDelete('set null');
            
            $table->index('external_system_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['external_system_id']);
            $table->dropIndex(['external_system_id']);
            $table->dropColumn('external_system_id');
        });
    }
};
