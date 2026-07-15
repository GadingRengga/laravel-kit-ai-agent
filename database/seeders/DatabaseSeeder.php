<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MenuSeeder::class,
            PermissionSeeder::class,
            MenuPermissionSeeder::class,
            SuperUserSeeder::class,
        ]);
    }
}
