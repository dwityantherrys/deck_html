<?php

namespace App\Models\Shipping;

use Illuminate\Database\Eloquent\Model;

class ShippingCost extends Model
{
    protected $fillable = ['min_length', 'max_length', 'min_weight', 'max_weight', 'charge_per_km'];
}
