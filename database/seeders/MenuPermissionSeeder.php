<?php

namespace Database\Seeders;

use App\Models\Superuser\Menu;
use App\Models\Superuser\Permission;
use Illuminate\Database\Seeder;

class MenuPermissionSeeder extends Seeder
{
    /**
     * Hubungkan permission yang sudah ada ke menu yang relevan.
     * Menu akan muncul di sidebar user jika user punya minimal 1
     * permission yang terhubung ke menu tersebut.
     */
    public function run(): void
    {
        $this->link('dashboard', ['dashboard.view']);

        $this->link('menu', [
            'menu.view',
            'menu.create',
            'menu.edit',
            'menu.delete',
        ]);
        $this->link('menus', [ // submenu dari user-management → arahkan ke menu-management
            'menu.view',
            'menu.create',
            'menu.edit',
            'menu.delete',
        ]);

        $this->link('role', [
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
        ]);
        $this->link('roles', [
            'role.view',
            'role.create',
            'role.edit',
            'role.delete',
        ]);

        $this->link('permission', [
            'permission.view',
            'permission.create',
            'permission.edit',
            'permission.delete',
        ]);

        $this->link('user-role', [
            'user-role.assign',
        ]);

        $this->link('users', [
            'user-role.assign',
        ]);

        $this->link('settings', [
            'ai.chat',
            'ai.connect',
        ]);

        $this->link('employees', [
            'dashboard.view',
        ]);
        $this->link('departments', [
            'dashboard.view',
        ]);
        $this->link('positions', [
            'dashboard.view',
        ]);

        $this->command?->info('MenuPermissionSeeder: relasi menu-permission berhasil diset.');
    }


    private function link(string $menuSlug, array $permissionSlugs): void
    {
        $menu = Menu::where('slug', $menuSlug)->first();
        if (! $menu) {
            $this->command?->warn("Menu [{$menuSlug}] tidak ditemukan, dilewati.");

            return;
        }

        $permissionIds = Permission::whereIn('slug', $permissionSlugs)->pluck('id')->all();
        $menu->permissions()->syncWithoutDetaching($permissionIds);
    }
}
