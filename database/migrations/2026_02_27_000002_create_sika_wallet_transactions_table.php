<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sika_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wallet_id')->constrained('sika_wallets')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->enum('type', [
                'DEPOSIT',
                'WITHDRAWAL',
                'SIKA_COIN_PURCHASE',
                'SIKA_COIN_CASHOUT',
                'TRANSFER_IN',
                'TRANSFER_OUT',
                'REFUND',
                'ADJUSTMENT',
            ]);
            
            $table->enum('direction', ['CREDIT', 'DEBIT']);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_before', 15, 2);
            $table->decimal('balance_after', 15, 2);
            
            $table->enum('status', ['PENDING', 'COMPLETED', 'FAILED', 'REVERSED'])->default('PENDING');
            
            $table->string('idempotency_key')->unique();
            $table->string('reference')->nullable();
            $table->string('external_reference')->nullable();
            
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            
            $table->index(['wallet_id', 'status', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['reference']);
            $table->index(['external_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sika_wallet_transactions');
    }
};
