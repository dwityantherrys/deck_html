<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Arr;

use App\Models\RoleMenu;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        
        Gate::define('access-menu', function ($user, $menuKey) {
            $menus = \Config::get('adminlte.menu');

            $menu = Arr::first($menus, function ($menu, $key) use ($menuKey) {
                if(is_string($menu)) return false;
                
                return $menu['model'] === $menuKey;
            });

            /**
             * jika menu yang di cek adalah parent menu, check submenunya. 
             * access parent tergantung dari submenunya
             */
            if(!empty($menu['submenu'])) {
                $submenuHasAccess = 0;

                foreach ($menu['submenu'] as $submenu) {
                    $hasAccess = RoleMenu::where([
                        'role_id' => $user->role_id,
                        'menu_key' => $submenu['model']
                    ])->first();

                    if($hasAccess) $submenuHasAccess++;
                }

                return !empty($submenuHasAccess);
            }

            // jika bukan parent check langsung
            $hasAccess = RoleMenu::where([
                'role_id' => $user->role_id,
                'menu_key' => $menuKey
            ])->first();

            return $hasAccess;
        });
    }
}
