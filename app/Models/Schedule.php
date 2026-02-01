<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'name',
        'starts_at',
        'ends_at',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'starts_at' => 'datetime:H:i',
        'ends_at' => 'datetime:H:i',
    ];

    public function tripTickets()
    {
        return $this->hasMany(TripTicket::class);
    }
}
