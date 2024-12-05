<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyItemReviewTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        App\Models\Master\Item\ItemReview::truncate();

        factory(App\Models\Master\Item\ItemReview::class, 15)->create();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
