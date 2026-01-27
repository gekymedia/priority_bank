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
        Schema::table('systems_registry', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('active_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systems_registry', function (Blueprint $table) {
            $table->dropColumn('is_protected');
        });
    }
};
