<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceMessage extends Model
{
    protected $fillable = [
        'title',
        'body',
        'level',
        'starts_at',
        'ends_at',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
