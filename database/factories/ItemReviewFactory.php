<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Models\Master\Item\Item;
use App\Models\Master\Item\ItemReview;

use Faker\Generator as Faker;

$factory->define(ItemReview::class, function (Faker $faker) {
  $item = Item::inRandomOrder()->limit(10)->first();

  return [
    'comment' => Arr::random([$faker->sentence, null]),
    'rate' => Arr::random([null, 1, 2, 3, 4, 5]),
    'item_id' => $item->id,
  ];
});
