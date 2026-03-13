<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['current_city_id'];

    public function currentCity()
    {
        return $this->belongsTo(City::class, 'current_city_id');
    }
}
