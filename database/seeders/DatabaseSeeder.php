<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a default user for testing/development
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create a default account for the test user
        \App\Models\Account::create([
            'user_id' => $user->id,
            'name' => 'Cash in Hand',
            'type' => 'cash',
            'opening_balance' => 0,
        ]);

        // Seed interest rates
        $this->call(InterestRateSeeder::class);

        // Seed test users
        $this->call(TestUsersSeeder::class);

        // Seed systems registry
        $this->call(SystemsRegistrySeeder::class);

        // Seed categories (Loan, Savings for Priority Bank)
        $this->call(CategorySeeder::class);

        // Create group funds record
        \App\Models\GroupFund::create([
            'total_available' => 0,
            'total_loaned' => 0,
            'total_savings' => 0,
            'last_updated' => now(),
        ]);
    }
}
