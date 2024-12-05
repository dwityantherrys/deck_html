<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // custom helpers
        require_once app_path() . '/Helpers/TransAction.php';
        require_once app_path() . '/Helpers/PrintFile.php';
        require_once app_path() . '/Helpers/RunningNumber.php';
        require_once app_path() . '/Helpers/Rupiah.php';
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */

     public function boot()
     {
         Schema::defaultStringLength(191);
     }
}
