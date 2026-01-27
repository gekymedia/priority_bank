<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add 'failed' and keep 'withdrawn' in the enum
        DB::statement("ALTER TABLE `savings` MODIFY COLUMN `status` ENUM('pending', 'successful', 'failed', 'withdrawn') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum (without failed)
        DB::statement("ALTER TABLE `savings` MODIFY COLUMN `status` ENUM('pending', 'successful', 'withdrawn') DEFAULT 'pending'");
    }
};
