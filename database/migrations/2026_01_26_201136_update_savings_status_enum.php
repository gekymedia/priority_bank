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
        // First, modify the enum column to include new values
        DB::statement("ALTER TABLE `savings` MODIFY COLUMN `status` ENUM('pending', 'successful', 'failed', 'withdrawn', 'available', 'locked') DEFAULT 'pending'");
        
        // Then update existing records
        DB::table('savings')
            ->where('status', 'locked')
            ->update(['status' => 'pending']);
            
        DB::table('savings')
            ->where('status', 'available')
            ->update(['status' => 'successful']);

        // Finally, remove old enum values
        DB::statement("ALTER TABLE `savings` MODIFY COLUMN `status` ENUM('pending', 'successful', 'failed') DEFAULT 'pending'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert existing records
        DB::table('savings')
            ->where('status', 'pending')
            ->update(['status' => 'locked']);
            
        DB::table('savings')
            ->where('status', 'successful')
            ->update(['status' => 'available']);
            
        DB::table('savings')
            ->where('status', 'failed')
            ->update(['status' => 'withdrawn']);

        // Revert the enum column
        DB::statement("ALTER TABLE `savings` MODIFY COLUMN `status` ENUM('available', 'withdrawn', 'locked') DEFAULT 'available'");
    }
};
