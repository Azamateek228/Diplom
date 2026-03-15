<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'city_id',
        'movie_id',
        'visit_date',
        'day_of_week',
        'venue',
        'show_time',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
