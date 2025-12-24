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
        Schema::table('users', function (Blueprint $table) {
            // Additional user fields for multi-user finance tracking
            $table->string('phone')->nullable()->after('email');
            $table->string('preferred_currency')->default('GHS')->after('phone');
            $table->boolean('notification_email')->default(true)->after('preferred_currency');
            $table->boolean('notification_browser')->default(true)->after('notification_email');
            $table->enum('theme', ['light', 'dark'])->default('light')->after('notification_browser');
            $table->string('role')->default('user')->after('theme');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'preferred_currency',
                'notification_email',
                'notification_browser',
                'theme',
                'role',
            ]);
        });
    }
};