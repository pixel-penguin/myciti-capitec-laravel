<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    protected $fillable = [
        'schedule_id',
        'bus_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
        'accessed_at',
        'metadata',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
