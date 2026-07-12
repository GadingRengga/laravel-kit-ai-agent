<?php

namespace Database\Seeders;

use App\Models\Superuser\Menu;
use App\Models\Superuser\Role;
use Illuminate\Database\Seeder;

class RoleHasMenuSeeder extends Seeder
{
    public function run(): void
    {
        $superUser = Role::where('slug', 'super_user')->first();

        if (!$superUser) {
            return;
        }

        $menus = Menu::all();

        foreach ($menus as $menu) {
            $superUser->menus()->syncWithoutDetaching([
                $menu->id => [
                    'can_view'   => true,
                    'can_create' => true,
                    'can_edit'   => true,
                    'can_delete' => true,
                ],
            ]);
        }
    }
}
