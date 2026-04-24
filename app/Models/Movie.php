<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Movie extends Model
{
    protected $casts = [
        'show_at' => 'datetime',
    ];
    protected $fillable = [
        'title',
        'genre',
        'duration',
        'age_rating',
        'poster',
        'description',
        'city_id',
        'venue',
        'expected_attendees',
        'show_at',
        'ticket_price',
    ];

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
