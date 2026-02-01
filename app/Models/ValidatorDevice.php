<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidatorDevice extends Model
{
    protected $fillable = [
        'name',
        'api_key_hash',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
