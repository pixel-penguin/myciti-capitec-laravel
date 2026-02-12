<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccessRequest extends Model
{
    protected $fillable = [
        'email',
        'name',
        'employee_id',
        'department',
        'status',
        'reason',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
        'employee_eligibility_id',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function employeeEligibility()
    {
        return $this->belongsTo(EmployeeEligibility::class);
    }
}
