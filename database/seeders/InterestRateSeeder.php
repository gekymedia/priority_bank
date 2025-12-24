<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class InterestRateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\InterestRate::insert([
            [
                'name' => 'Standard Loan Rate',
                'rate_percentage' => 5.00,
                'type' => 'loan_interest',
                'is_active' => true,
                'effective_from' => now(),
                'description' => 'Standard interest rate for credit union loans',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Premium Loan Rate',
                'rate_percentage' => 7.50,
                'type' => 'loan_interest',
                'is_active' => true,
                'effective_from' => now(),
                'description' => 'Higher interest rate for larger loans',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Savings Interest Rate',
                'rate_percentage' => 3.00,
                'type' => 'savings_interest',
                'is_active' => true,
                'effective_from' => now(),
                'description' => 'Interest rate for savings deposits',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
