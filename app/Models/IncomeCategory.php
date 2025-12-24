<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\IncomeCategory
 *
 * This model represents a source of income. Categories can be defined per user or be global
 * (where the user_id is null). Each income belongs to a category.
 */
class IncomeCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
    ];

    /**
     * The incomes that belong to this category.
     */
    public function incomes()
    {
        return $this->hasMany(Income::class);
    }
}
