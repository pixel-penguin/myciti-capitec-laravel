<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmployeeEligibility extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'email',
        'first_name',
        'last_name',
        'phone',
        'status',
        'source',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
