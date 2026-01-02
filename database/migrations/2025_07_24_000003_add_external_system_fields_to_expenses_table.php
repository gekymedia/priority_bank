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
        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('external_system_id')->nullable()->after('user_id')
                ->constrained('systems_registry')->nullOnDelete();
            $table->string('external_transaction_id')->nullable()->after('external_system_id');
            $table->string('idempotency_key')->nullable()->unique()->after('external_transaction_id');
            $table->enum('sync_status', ['pending', 'synced', 'failed'])->default('pending')->after('idempotency_key');
            $table->timestamp('synced_at')->nullable()->after('sync_status');
            $table->text('sync_error')->nullable()->after('synced_at');
            
            $table->index(['external_system_id', 'external_transaction_id']);
            $table->index('idempotency_key');
            $table->index('sync_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['external_system_id']);
            $table->dropIndex(['external_system_id', 'external_transaction_id']);
            $table->dropIndex(['idempotency_key']);
            $table->dropIndex(['sync_status']);
            $table->dropColumn([
                'external_system_id',
                'external_transaction_id',
                'idempotency_key',
                'sync_status',
                'synced_at',
                'sync_error',
            ]);
        });
    }
};

