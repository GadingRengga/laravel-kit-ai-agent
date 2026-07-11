<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super User', 'slug' => 'super_user', 'description' => 'Akses penuh ke seluruh sistem'],
            ['name' => 'Administrator', 'slug' => 'admin', 'description' => 'Mengelola operasional harian'],
            ['name' => 'Manager', 'slug' => 'manager', 'description' => 'Akses laporan & approval'],
            ['name' => 'Staff', 'slug' => 'staff', 'description' => 'Akses menu operasional dasar'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
