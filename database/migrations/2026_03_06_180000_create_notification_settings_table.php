<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed default channel toggles (enabled = 1)
        DB::table('notification_settings')->insert([
            ['key' => 'channel_email_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'channel_sms_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'channel_whatsapp_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'channel_gekychat_enabled', 'value' => '1', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
