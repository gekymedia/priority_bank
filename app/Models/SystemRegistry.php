<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SystemRegistry extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'systems_registry';

    protected $fillable = [
        'system_id',
        'name',
        'type',
        'callback_url',
        'api_base_url',
        'active_status',
        'description',
        'metadata',
    ];

    protected $casts = [
        'active_status' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get all active systems.
     */
    public function scopeActive($query)
    {
        return $query->where('active_status', true);
    }

    /**
     * Get systems by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}

