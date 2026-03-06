<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // sika_wallets: only modify if still using user_id (idempotent)
        if (Schema::hasTable('sika_wallets')) {
            $walletColumns = Schema::getColumnListing('sika_wallets');
            if (in_array('user_id', $walletColumns) && !in_array('external_user_id', $walletColumns)) {
                try {
                    Schema::table('sika_wallets', function (Blueprint $table) {
                        $table->dropForeign(['user_id']);
                    });
                } catch (\Throwable $e) {
                    // FK may already be dropped
                }
                Schema::table('sika_wallets', function (Blueprint $table) {
                    $table->renameColumn('user_id', 'external_user_id');
                });
            }
            if (!in_array('source', $walletColumns)) {
                Schema::table('sika_wallets', function (Blueprint $table) {
                    $table->string('source', 50)->default('gekychat')->after('external_user_id');
                });
            }
            // Drop old unique index if it exists (single column), add composite if missing
            try {
                Schema::table('sika_wallets', function (Blueprint $table) {
                    $table->dropUnique(['external_user_id']);
                });
            } catch (\Throwable $e) {
                // Index may already be dropped or named differently
            }
            try {
                Schema::table('sika_wallets', function (Blueprint $table) {
                    $table->unique(['external_user_id', 'source']);
                });
            } catch (\Throwable $e) {
                // Composite unique may already exist
            }
        }

        // sika_wallet_transactions: only modify if still using user_id (idempotent)
        if (Schema::hasTable('sika_wallet_transactions')) {
            $txColumns = Schema::getColumnListing('sika_wallet_transactions');
            if (in_array('user_id', $txColumns) && !in_array('external_user_id', $txColumns)) {
                Schema::table('sika_wallet_transactions', function (Blueprint $table) {
                    $table->renameColumn('user_id', 'external_user_id');
                });
            }
            if (!in_array('source', $txColumns)) {
                Schema::table('sika_wallet_transactions', function (Blueprint $table) {
                    $table->string('source', 50)->default('gekychat')->after('external_user_id');
                });
            }
        }
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
