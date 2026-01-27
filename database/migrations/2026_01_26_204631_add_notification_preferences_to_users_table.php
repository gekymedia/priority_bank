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
            $table->boolean('notification_sms')->default(true)->after('notification_browser');
            $table->boolean('notification_whatsapp')->default(true)->after('notification_sms');
            $table->boolean('notification_gekychat')->default(true)->after('notification_whatsapp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['notification_sms', 'notification_whatsapp', 'notification_gekychat']);
        });
    }
};
