<?php

namespace Database\Seeders;

use App\Models\Superuser\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $dashboard = Menu::updateOrCreate(
            ['slug' => 'dashboard'],
            ['name' => 'Dashboard', 'icon' => 'fa-solid fa-gauge', 'route' => 'dashboard', 'order' => 1]
        );

        $masterData = Menu::updateOrCreate(
            ['slug' => 'master-data'],
            ['name' => 'Data Master', 'icon' => 'fa-solid fa-database', 'order' => 2]
        );

        Menu::updateOrCreate(
            ['slug' => 'employees'],
            ['name' => 'Karyawan', 'icon' => 'fa-solid fa-id-badge', 'route' => 'employees', 'parent_id' => $masterData->id, 'order' => 1]
        );

        Menu::updateOrCreate(
            ['slug' => 'departments'],
            ['name' => 'Departemen', 'icon' => 'fa-solid fa-sitemap', 'route' => 'departments', 'parent_id' => $masterData->id, 'order' => 2]
        );

        Menu::updateOrCreate(
            ['slug' => 'positions'],
            ['name' => 'Jabatan', 'icon' => 'fa-solid fa-briefcase', 'route' => 'positions', 'parent_id' => $masterData->id, 'order' => 3]
        );

        $userManagement = Menu::updateOrCreate(
            ['slug' => 'user-management'],
            ['name' => 'Manajemen Pengguna', 'icon' => 'fa-solid fa-users-gear', 'order' => 3]
        );

        Menu::updateOrCreate(
            ['slug' => 'users'],
            ['name' => 'Pengguna', 'icon' => 'fa-solid fa-user', 'route' => 'superuser/user', 'parent_id' => $userManagement->id, 'order' => 1]
        );

        Menu::updateOrCreate(
            ['slug' => 'roles'],
            ['name' => 'Role', 'icon' => 'fa-solid fa-user-shield', 'route' => 'superuser/role', 'parent_id' => $userManagement->id, 'order' => 2]
        );

        Menu::updateOrCreate(
            ['slug' => 'permission'],
            ['name' => 'Permission', 'icon' => 'fa-solid fa-key', 'route' => 'superuser/permission', 'parent_id' => $userManagement->id, 'order' => 3]
        );

        Menu::updateOrCreate(
            ['slug' => 'menus'],
            ['name' => 'Menu Sistem', 'icon' => 'fa-solid fa-bars', 'route' => 'superuser/menu', 'parent_id' => $userManagement->id, 'order' => 4]
        );

        Menu::updateOrCreate(
            ['slug' => 'user-role'],
            ['name' => 'Role User', 'icon' => 'fa-solid fa-users-gear', 'route' => 'superuser/user-role', 'parent_id' => $userManagement->id, 'order' => 5]
        );

        Menu::updateOrCreate(
            ['slug' => 'settings'],
            ['name' => 'Pengaturan', 'icon' => 'fa-solid fa-gear', 'route' => 'settings', 'order' => 4]
        );
    }
}
