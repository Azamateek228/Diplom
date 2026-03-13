<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class City extends Model
{
    protected $fillable = ['name', 'lat', 'lng'];

     public function votes()
    {
        return $this->hasMany(\App\Models\Vote::class);
    }
}
