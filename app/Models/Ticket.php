<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'user_id',
        'city_id',
        'movie_id',
        'show_date',
        'show_time',
        'quantity',
        'unit_price',
        'total_price',
        'status',
        'payment_method',
        'payment_reference',
        'qr_token',
        'refunded_at',
    ];

    protected $casts = [
        'show_date' => 'date',
        'refunded_at' => 'datetime',
    ];

    public function getShowDateTimeAttribute(): Carbon
    {
        return Carbon::parse($this->show_date->toDateString() . ' ' . $this->show_time);
    }

    public function getRefundAvailableUntilAttribute(): Carbon
    {
        return $this->show_date_time->copy()->subHours(24);
    }

    public function getCanRefundAttribute(): bool
    {
        return $this->status === 'purchased'
            && now()->lessThan($this->refund_available_until);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function movie()
    {
        return $this->belongsTo(Movie::class);
    }
}
