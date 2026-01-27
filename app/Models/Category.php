<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type', // 'income', 'expense', 'both'
    ];

    /**
     * Get transactions for this category (income)
     */
    public function incomeTransactions()
    {
        return \App\Models\Transaction::where('category', $this->name)
            ->where('type', 'income');
    }

    /**
     * Get transactions for this category (expense)
     */
    public function expenseTransactions()
    {
        return \App\Models\Transaction::where('category', $this->name)
            ->where('type', 'expense');
    }

    /**
     * Check if category can be used for income
     */
    public function canBeUsedForIncome()
    {
        return in_array($this->type, ['income', 'both']);
    }

    /**
     * Check if category can be used for expense
     */
    public function canBeUsedForExpense()
    {
        return in_array($this->type, ['expense', 'both']);
    }
}
