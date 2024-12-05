<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use Faker\Generator as Faker;

use App\Models\Role;
use App\Models\Chat\ChatMessage;

$factory->define(ChatMessage::class, function (Faker $faker) {
    $roleAsAdmin = Role::where('name', 'super_admin')->first()->id;
    $employee = App\User::where('role_id', $roleAsAdmin)->first();
    $header = App\Models\Chat\ChatHeader::inRandomOrder()->limit(10)->first();
    $sender = Arr::random([ChatMessage::SEND_BY_EMPLOYEE, ChatMessage::SEND_BY_CUSTOMER]);
    $senderId = ($sender === ChatMessage::SEND_BY_EMPLOYEE) ? $employee->id : NULL;

    return [
      'chat_header_id' => $header->id,
      'message' => $faker->sentence,
      'sender' => $sender,
      'sender_id' => $senderId
    ];
});
