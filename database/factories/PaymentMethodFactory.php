<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Master\Payment\PaymentMethod;

$factory->define(PaymentMethod::class, function () {
  return [
      'id' => 0,
      'name' => 'Cash'
  ];
});
