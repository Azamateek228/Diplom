<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movie extends Model
{
    protected $fillable = [
        'title',
        'genre',
        'duration',
        'age_rating',
        'poster',
        'description',
        'city_id',
        'venue',
        'show_time',
        'venue_capacity',
        'expected_attendees',
    ];

    protected $casts = [
        'show_time' => 'datetime',
    ];

    protected $appends = ['poster_url'];

    public function getPosterUrlAttribute(): string
    {
        $fallback = asset('images/poster-placeholder.svg');

        if (! $this->poster) {
            return $fallback;
        }

        if (str_starts_with($this->poster, 'images/')) {
            return file_exists(public_path($this->poster))
                ? asset($this->poster)
                : $fallback;
        }

        return file_exists(storage_path('app/public/' . $this->poster))
            ? asset('storage/' . $this->poster)
            : $fallback;
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}

