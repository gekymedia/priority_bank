<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Links systems_registry entries to their corresponding system user accounts.
     * This allows each source (GekyChat, SchoolsGH, etc.) to have its own
     * user account for balance tracking and transactions.
     */
    public function up(): void
    {
        Schema::table('systems_registry', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('system_id')
                ->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systems_registry', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
