<?php

use Illuminate\Database\Seeder;

class MenuRoleSeeder extends Seeder
{
  /**
  * Run the database seeds.
  *
  * @return void
  */
  public function run()
  {
    //
    App\Models\RoleMenu::truncate();

    $roleAsSuperAdmin = App\Models\Role::where('name', 'super_admin')->first();

    foreach (\Config::get('adminlte.menu') as $menu) {
      if(is_string($menu)) {
        $roleAsSuperAdmin->menus()->create(['menu_key' => \Str::slug($menu, '_')]);
        continue;
      };

      $roleAsSuperAdmin->menus()->create(['menu_key' => $menu['model']]);
      /* get submenu */
      if(!empty($menu['submenu'])) {
        foreach ($menu['submenu'] as $submenu) {
          $roleAsSuperAdmin->menus()->create(['menu_key' => $submenu['model']]);
        }
      }
    }
  }
}
