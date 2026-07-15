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

        $user = User::updateOrCreate(
            ['email' => 'superuser@netra.local'],
            [
                'name'     => 'Super User',
                'username' => 'superuser',
                'password' => Hash::make('password'), // WAJIB diganti setelah deploy!
                'is_active' => true,
            ]
        );

        // Attach super_user role ke user ini (many-to-many)
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }
    }
}
