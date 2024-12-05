<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */
use Faker\Generator as Faker;

use App\Models\Role;
use App\Models\Chat\ChatHeader;

$factory->define(ChatHeader::class, function (Faker $faker) {
    $roleAsCustomer = Role::where('name', 'customer')->first()->id;

    return [
      'chat_type_id' => App\Models\Chat\ChatType::where('code', 'GEN')->first()->id,
      'customer_id' => App\User::inRandomOrder()->where('role_id', $roleAsCustomer)->limit(10)->first()->id
    ];
});
