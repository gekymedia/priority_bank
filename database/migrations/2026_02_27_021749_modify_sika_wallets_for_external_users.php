<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the foreign key constraint and modify the column
        Schema::table('sika_wallets', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['user_id']);
            
            // Rename user_id to external_user_id
            $table->renameColumn('user_id', 'external_user_id');
        });

        Schema::table('sika_wallets', function (Blueprint $table) {
            // Add source column to identify which system the user is from
            $table->string('source', 50)->default('gekychat')->after('external_user_id');
            
            // Drop the old unique index
            $table->dropUnique(['external_user_id']);
            
            // Create a composite unique index on external_user_id + source
            $table->unique(['external_user_id', 'source']);
        });

        // Also update the transactions table
        Schema::table('sika_wallet_transactions', function (Blueprint $table) {
            $table->renameColumn('user_id', 'external_user_id');
        });

        Schema::table('sika_wallet_transactions', function (Blueprint $table) {
            $table->string('source', 50)->default('gekychat')->after('external_user_id');
        });
    }

    public function down(): void
    {
        Schema::table('sika_wallet_transactions', function (Blueprint $table) {
            $table->dropColumn('source');
            $table->renameColumn('external_user_id', 'user_id');
        });

        Schema::table('sika_wallets', function (Blueprint $table) {
            $table->dropUnique(['external_user_id', 'source']);
            $table->dropColumn('source');
            $table->renameColumn('external_user_id', 'user_id');
            $table->unique('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }
};
