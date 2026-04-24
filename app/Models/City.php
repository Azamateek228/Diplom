<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'lat', 'lng', 'population'];

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    public function ticketLimit(): int
    {
        $population = (int) ($this->population ?? 0);

        if ($population >= 1000000) {
            return 300;
        }

        if ($population >= 300000) {
            return 150;
        }

        return 50;
    }
}
