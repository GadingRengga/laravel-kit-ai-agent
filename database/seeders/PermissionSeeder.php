<?php

namespace Database\Seeders;

use App\Models\Superuser\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Daftar permission awal yang dibutuhkan sistem.
     * Setiap permission bisa dihubungkan ke banyak menu.
     * Pola penamaan: {context}.{action} — mis. menu.view, employee.create
     */
    public function run(): void
    {
        $permissions = [
            // Dashboard
            ['name' => 'Lihat Dashboard', 'slug' => 'dashboard.view', 'group' => 'dashboard', 'description' => 'Melihat halaman dashboard'],

            // Manajemen Menu
            ['name' => 'Lihat Menu Sistem', 'slug' => 'menu.view', 'group' => 'menu', 'description' => 'Melihat daftar menu sidebar'],
            ['name' => 'Tambah Menu', 'slug' => 'menu.create', 'group' => 'menu', 'description' => 'Menambahkan menu baru'],
            ['name' => 'Ubah Menu', 'slug' => 'menu.edit', 'group' => 'menu', 'description' => 'Mengubah menu existing'],
            ['name' => 'Hapus Menu', 'slug' => 'menu.delete', 'group' => 'menu', 'description' => 'Menghapus menu'],

            // Manajemen Role
            ['name' => 'Lihat Role', 'slug' => 'role.view', 'group' => 'role', 'description' => 'Melihat daftar role'],
            ['name' => 'Tambah Role', 'slug' => 'role.create', 'group' => 'role', 'description' => 'Menambahkan role baru'],
            ['name' => 'Ubah Role', 'slug' => 'role.edit', 'group' => 'role', 'description' => 'Mengubah role & permission-nya'],
            ['name' => 'Hapus Role', 'slug' => 'role.delete', 'group' => 'role', 'description' => 'Menghapus role'],

            // Manajemen Permission
            ['name' => 'Lihat Permission', 'slug' => 'permission.view', 'group' => 'permission', 'description' => 'Melihat daftar permission'],
            ['name' => 'Tambah Permission', 'slug' => 'permission.create', 'group' => 'permission', 'description' => 'Menambahkan permission baru'],
            ['name' => 'Ubah Permission', 'slug' => 'permission.edit', 'group' => 'permission', 'description' => 'Mengubah permission & tautan menu'],
            ['name' => 'Hapus Permission', 'slug' => 'permission.delete', 'group' => 'permission', 'description' => 'Menghapus permission'],

            // Role User (assign role ke user)
            ['name' => 'Atur Role User', 'slug' => 'user-role.assign', 'group' => 'user-role', 'description' => 'Mengatur role yang dimiliki user'],

            // AI Assistant
            ['name' => 'Akses AI Chat', 'slug' => 'ai.chat', 'group' => 'ai', 'description' => 'Mengakses halaman AI Chat'],
            ['name' => 'Hubungkan AI', 'slug' => 'ai.connect', 'group' => 'ai', 'description' => 'Menghubungkan akun AI (API key)'],
        ];

        foreach ($permissions as $perm) {
            Permission::updateOrCreate(['slug' => $perm['slug']], $perm);
        }

        $this->command?->info('PermissionSeeder: ' . count($permissions) . ' permission berhasil dibuat.');
    }
}
