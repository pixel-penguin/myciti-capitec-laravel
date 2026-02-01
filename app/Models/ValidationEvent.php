<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ValidationEvent extends Model
{
    protected $fillable = [
        'user_id',
        'trip_ticket_id',
        'schedule_id',
        'bus_id',
        'event_type',
        'scanned_at',
        'validator_id',
        'metadata',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tripTicket()
    {
        return $this->belongsTo(TripTicket::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }
}
