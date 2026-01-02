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
        Schema::create('systems_registry', function (Blueprint $table) {
            $table->id();
            $table->string('system_id')->unique(); // e.g., 'gekymedia', 'priority_solutions_agency'
            $table->string('name'); // Display name
            $table->enum('type', ['manual', 'automated', 'hybrid'])->default('hybrid');
            $table->string('callback_url')->nullable(); // Webhook URL for pushing data back
            $table->string('api_base_url')->nullable(); // Base URL for API calls
            $table->boolean('active_status')->default(true);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional system-specific data
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('system_id');
            $table->index('active_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('systems_registry');
    }
};

