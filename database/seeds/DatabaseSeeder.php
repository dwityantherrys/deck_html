<?php
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      DB::statement('SET FOREIGN_KEY_CHECKS = 0'); // disable foreign key constraints


      App\Models\Role::truncate();
      App\Models\Role::insert([
        [ 'name' => 'super_admin', 'display_name' => 'Super Admin' ],
        [ 'name' => 'customer', 'display_name' => 'Customer' ],
        [ 'name' => 'employee', 'display_name' => 'Employee' ],
      ]);

      $roleAsSuperAdmin = App\Models\Role::where('name', 'super_admin')->first();

      App\User::truncate();
      App\Models\Master\TermOfService::truncate();
      App\Models\Master\Payment\PaymentMethod::truncate();
      App\Models\Chat\ChatType::truncate();


      App\User::insert([
          'name' => 'ERP Administrator',
          'email' => 'admin@demoerp.web.id',
          'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
          'role_id' => $roleAsSuperAdmin->id
      ]);

      App\Models\Master\Payment\PaymentMethod::insert([
        [ 'name' => 'Cash' ],
        [ 'name' => 'Transfer' ],
        [ 'name' => 'Transfer with DP' ],
        [ 'name' => 'Pay Later' ]
      ]);

      App\Models\Master\Payment\PaymentBankChannel::insert([
        [
          'name' => 'BCA',
          'rekening_name' => 'PT. AMAK PASUKAN KATAK',
          'rekening_number' => '555.033.0077'
        ],
      ]);

      App\Models\Master\TermOfService::insert([
        [ 'title' => 'Application', 'slug' => 'application', 'term' => '' ],
        [ 'title' => 'Apply Paylater', 'slug' => 'paylater', 'term' => '' ]
        // [ 'title' => 'Shipping', 'slug' => 'shipping', 'term' => '' ]
      ]);

      App\Models\Chat\ChatType::insert([
        [ 'name' => 'General', 'code' => 'GEN', 'description' => 'diskusi umum' ],
        [ 'name' => 'Product', 'code' => 'PROD', 'description' => 'diskusi seputar produk' ],
        [ 'name' => 'Transaction', 'code' => 'TRAN', 'description' => 'diskusi seputar transaksi' ]
      ]);

      $this->call([
        // CityTableSeeder::class,
		MenuRoleSeeder::class,
        // DummyChatTableSeeder::class,
        DummyInventoryTableSeeder::class,
        DummyItemReviewTableSeeder::class,
    ]);

      DB::statement('SET FOREIGN_KEY_CHECKS = 1'); // enable foreign key constraints
    }
}
