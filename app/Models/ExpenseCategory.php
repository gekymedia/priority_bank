<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ExpenseCategory
 *
 * Represents a category of expense. Categories may be global (user_id null) or user-specific.
 */
class ExpenseCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * Get the expenses for this category.
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Budgets associated with this category.
     */
    public function budgets()
    {
        return $this->hasMany(Budget::class);
    }
}
