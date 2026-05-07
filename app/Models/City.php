<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'lat', 'lng', 'population'];

    public function movies()
    {
        return $this->hasMany(Movie::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }
}
