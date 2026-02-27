<?php

namespace Database\Seeders;

use App\Models\SystemRegistry;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SystemAccountsSeeder extends Seeder
{
    /**
     * Create system user accounts for each source in systems_registry.
     * 
     * This seeder:
     * 1. Gets all active sources from systems_registry
     * 2. Creates a system user account for each (if not already exists)
     * 3. Links the user account to the systems_registry entry
     */
    public function run(): void
    {
        $sources = SystemRegistry::whereNull('user_id')
            ->where('active_status', true)
            ->get();

        $this->command->info("Found {$sources->count()} sources without user accounts.");

        foreach ($sources as $source) {
            DB::transaction(function () use ($source) {
                // Generate system email and phone
                $systemEmail = "system.{$source->system_id}@prioritybank.internal";
                $systemPhone = "SYSTEM-{$source->system_id}";

                // Check if user already exists (by email or phone)
                $existingUser = User::where('email', $systemEmail)
                    ->orWhere('phone', $systemPhone)
                    ->first();

                if ($existingUser) {
                    $this->command->warn("User already exists for {$source->name}, linking...");
                    $source->user_id = $existingUser->id;
                    $source->save();
                    return;
                }

                // Create the system user account
                $user = User::create([
                    'name' => "{$source->name} (System Account)",
                    'email' => $systemEmail,
                    'phone' => $systemPhone,
                    'password' => Hash::make(Str::random(32)), // Random password (not used)
                    'role' => 'user', // Not admin
                    'type' => 'system',
                    'status' => 'approved',
                    'preferred_currency' => 'GHS',
                    'notification_email' => false,
                    'notification_browser' => false,
                    'notification_sms' => false,
                    'notification_whatsapp' => false,
                    'notification_gekychat' => false,
                ]);

                // Link the user to the source
                $source->user_id = $user->id;
                $source->save();

                $this->command->info("Created system account for {$source->name} (User ID: {$user->id})");
            });
        }

        $this->command->info('System accounts seeding completed.');
        
        // Display summary
        $this->displaySummary();
    }

    /**
     * Display a summary of all system accounts
     */
    private function displaySummary(): void
    {
        $this->command->newLine();
        $this->command->info('=== System Accounts Summary ===');
        
        $sources = SystemRegistry::with('user')
            ->where('active_status', true)
            ->get();

        foreach ($sources as $source) {
            $userId = $source->user_id ?? 'NOT LINKED';
            $userName = $source->user?->name ?? 'N/A';
            $this->command->line("  [{$source->system_id}] {$source->name} => User ID: {$userId} ({$userName})");
        }
    }
}
