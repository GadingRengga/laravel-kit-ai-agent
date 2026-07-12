<?php

namespace Database\Seeders;

use App\Models\Superuser\Role;
use App\Models\Superuser\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperUserSeeder extends Seeder
{
    public function run(): void
    {
        $role = Role::where('slug', 'super_user')->first();

        User::updateOrCreate(
            ['email' => 'superuser@netra.local'],
            [
                'role_id'  => $role?->id,
                'name'     => 'Super User',
                'username' => 'superuser',
                'password' => Hash::make('password'), // WAJIB diganti setelah deploy!
                'is_active' => true,
            ]
        );
    }
}
