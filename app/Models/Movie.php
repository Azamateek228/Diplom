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
        if (! $this->poster) {
            return asset('images/poster-placeholder.jpg');
        }

        return str_starts_with($this->poster, 'images/')
            ? asset($this->poster)
            : asset('storage/' . $this->poster);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
