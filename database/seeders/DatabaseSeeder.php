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

        // Seed some global (user_id null) income categories
        \App\Models\IncomeCategory::insert([
            ['name' => 'Salary', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Business', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Gifts', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Seed some global expense categories
        \App\Models\ExpenseCategory::insert([
            ['name' => 'Food & Groceries', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Transport', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Utilities/Bills', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Rent', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Donations & Support', 'user_id' => null, 'created_at' => now(), 'updated_at' => now()],
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

        // Create group funds record
        \App\Models\GroupFund::create([
            'total_available' => 0,
            'total_loaned' => 0,
            'total_savings' => 0,
            'last_updated' => now(),
        ]);
    }
}
