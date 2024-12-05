<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */
use Faker\Generator as Faker;

use App\User;

$factory->define(User::class, function (Faker $faker) {
  $roleAsUser = App\Models\Role::where('name', 'customer')->first();
  
  $name = $faker->name;
  $email = Str::slug($name, '-') . '@example.com';
  return [
      'name' => $name,
      'email' => $email,
      'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
      'role_id' => $roleAsUser->id,
      'region_type' => 0,
      'region_id' => 444 //surabaya
  ];
});
