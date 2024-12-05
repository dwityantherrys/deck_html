<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogCityDistance extends Model
{
    protected $fillable = ['departure_id', 'arrival_id', 'distance_in_km'];
}
