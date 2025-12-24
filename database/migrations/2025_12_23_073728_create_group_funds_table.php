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
        Schema::create('group_funds', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_available', 15, 2)->default(0);
            $table->decimal('total_loaned', 15, 2)->default(0);
            $table->decimal('total_savings', 15, 2)->default(0);
            $table->date('last_updated');
            $table->json('fund_breakdown')->nullable(); // Store breakdown by source
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_funds');
    }
};
