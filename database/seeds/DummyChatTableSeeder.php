<?php

use Illuminate\Database\Seeder;

class DummyChatTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker\Factory::create();
        $roleAsUser = App\Models\Role::where('name', 'customer')->first();

        DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints

        App\Models\Chat\ChatHeader::truncate();
        App\Models\Chat\ChatMessage::truncate();

        factory(App\User::class, 10)->create();

        $dummyUsers = App\User::where('role_id', $roleAsUser->id)->get();
        foreach ($dummyUsers as $key => $user) {
            $profile = App\Models\Master\Profile\Profile::create([
                'name' => $user->name,
                'phone' => $faker->phoneNumber,
                'is_active' => true,
                'user_id' => $user->id
            ]);
    
            $profile->transaction_setting()->create([
                'payment_method_id'    => App\Models\Master\Profile\ProfileTransactionSetting::DEFAULT_PAYMENT_METHOD_MOBILE,
                'tempo_type'           => App\Models\Master\Profile\ProfileTransactionSetting::DEFAULT_TEMPO_TYPE,
                'created_by'           => $user->id
            ]);
        }

        factory(App\Models\Chat\ChatHeader::class, 10)->create();
        factory(App\Models\Chat\ChatMessage::class, 50)->create();

        DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
