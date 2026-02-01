<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripTicket extends Model
{
    protected $fillable = [
        'user_id',
        'schedule_id',
        'ticket_date',
        'qr_token',
        'status',
        'issued_at',
        'expires_at',
        'metadata',
    ];

    protected $casts = [
        'ticket_date' => 'date',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function schedule()
    {
        return $this->belongsTo(Schedule::class);
    }

    public function validationEvents()
    {
        return $this->hasMany(ValidationEvent::class);
    }
}
