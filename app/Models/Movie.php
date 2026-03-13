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
        'expected_attendees',
    ];

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
