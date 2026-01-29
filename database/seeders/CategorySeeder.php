<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Priority Bank specific categories
        $categories = [
            [
                'name' => 'Loan',
                'type' => 'expense', // Loans given out are expenses
            ],
            [
                'name' => 'Savings',
                'type' => 'income', // Savings deposits are income
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
