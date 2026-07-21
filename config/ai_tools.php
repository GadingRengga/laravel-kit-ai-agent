<?php

/*
|--------------------------------------------------------------------------
| Daftar "function" yang boleh dipanggil AI — DINAMIS, tanpa bikin class baru
|--------------------------------------------------------------------------
| Setiap kali kamu mau AI bisa bikin data baru (customer, order, dll), cukup
| tambah 1 array di sini. TIDAK perlu bikin App\Actions\... atau
| App\Services\AI\Tools\... class baru — GenericModelTool yang menangani
| semuanya berdasarkan config ini.
|
| Kalau nanti ada kasus yang butuh business logic lebih rumit dari sekadar
| "create Model baru" (misal: kirim notifikasi, generate nomor invoice, dst),
| baru saat itu bikin Tool class custom sendiri (lihat contoh lama
| CreateCustomerTool.php) dan daftarkan manual di AiServiceProvider — bukan
| lewat file ini.
|
| PENTING: kalau salah satu entry di bawah salah config (model tidak ada,
| field typo, dst), AI TIDAK AKAN CRASH untuk semua pesan seperti sebelumnya.
| Yang terjadi: entry itu dilewati (skip) saat register, dan kalau user minta
| sesuatu yang belum ada tool-nya, AI otomatis balas "belum bisa, hubungi
| developer" — bukan error 500. Lihat AiChatService::handleToolCall().
|
|--------------------------------------------------------------------------
| Batasan akses: 'permission'
|--------------------------------------------------------------------------
| Setiap tool bisa dibatasi aksesnya berdasarkan permission (slug).
| User harus memiliki permission tersebut (melalui role-nya) agar bisa
| menggunakan tool ini. Lihat User::hasPermission().
|
| Contoh: 'permission' => 'menu.create'
|
| Kosongkan atau hilangkan key `permission` kalau tool ini sengaja
| mau dibuka untuk SEMUA user yang sudah login.
|
| Legacy: key 'menu' + 'ability' masih didukung untuk backward compat,
| tapi disarankan beralih ke 'permission'.
*/

return [
    // 'create_user' SENGAJA TIDAK didaftarkan di sini. Tool ini butuh logic
    // resolve role by name/slug/ID yang tidak bisa dilakukan GenericModelTool
    // (cuma create() polos) — sudah ditangani App\Services\AI\Tools\CreateUserTool
    // dan didaftarkan manual di AiServiceProvider. Kalau kamu tambah entry
    // 'create_user' di sini lagi, dia akan SILENTLY DITIMPA oleh CreateUserTool
    // saat registrasi (nama tool sama = key array sama) — jadi jangan.


    /*
    |--------------------------------------------------------------------------
    | Contoh nambah tool baru — tinggal copy-paste blok di atas & sesuaikan.
    | Tidak perlu bikin file PHP baru sama sekali.
    |--------------------------------------------------------------------------
    */
    [
        'name' => 'create_user',
        'model' => \App\Models\Superuser\User::class,
        'menu' => 'user',
        'ability' => 'can_user',
        'description' => 'Membuat user baru dengan nama, email, username, password, dan role. Role bisa diisi dengan nama role (misal: "Staff", "Admin") atau ID role.',
        'summary_template' => 'Buat User baru',
        'fields' => [
            'name'     => ['type' => 'string', 'description' => 'Nama lengkap user'],
            'email'    => ['type' => 'string', 'format' => 'email', 'description' => 'Email user'],
            'username' => ['type' => 'string', 'description' => 'Username untuk login (opsional)'],
            'password' => ['type' => 'string', 'description' => 'Password untuk login (minimal 8 karakter)'],
            'role'     => ['type' => 'string', 'description' => 'Nama role (misal: Staff, Admin) atau ID role'],
            'is_active' => ['type' => 'boolean', 'description' => 'Status aktif user (default: true)'],
        ],
        'stamp_user_as' => 'created_by',
    ],
    [
        'name' => 'read_user',
        'model' => \App\Models\Superuser\User::class,
        'menu' => 'user',
        'ability' => 'can_user', // Atau bisa disesuaikan jika ada permission khusus view (misal: 'view_user')
        'description' => 'Mencari atau menampilkan data user/pengguna berdasarkan filter tertentu seperti nama, email, username, status aktif, atau mengambil satu user spesifik.',
        'summary_template' => 'Menampilkan data User',
        'fields' => [
            'id'        => ['type' => 'integer', 'description' => 'ID user spesifik (gunakan jika ingin melihat 1 data detail)'],
            'search'    => ['type' => 'string', 'description' => 'Kata kunci untuk mencari berdasarkan nama, email, atau username'],
            'role'      => ['type' => 'string', 'description' => 'Filter berdasarkan nama role tertentu'],
            'is_active' => ['type' => 'boolean', 'description' => 'Filter status aktif user (true untuk aktif, false untuk tidak aktif)'],
            'limit'     => ['type' => 'integer', 'description' => 'Membatasi jumlah data user yang ditampilkan (default: 10)'],
        ],
        'stamp_user_as' => null, // Tidak perlu stamp user karena hanya membaca data
    ],
    // [
    //     'name' => 'create_order',
    //     'model' => \App\Models\Order::class,
    //     'menu' => 'order',
    //     'ability' => 'can_create',
    //     'description' => 'Membuat order baru untuk customer yang sudah ada.',
    //     'summary_template' => 'Buat order baru untuk **:customer_name** senilai **:total**',
    //     'fields' => [
    //         'customer_name' => ['type' => 'string', 'required' => true, 'description' => 'Nama customer tujuan order'],
    //         'total'         => ['type' => 'number', 'required' => true, 'description' => 'Total nilai order dalam Rupiah'],
    //         'notes'         => ['type' => 'string', 'required' => false, 'description' => 'Catatan tambahan'],
    //     ],
    //     'stamp_user_as' => 'created_by',
    // ],

];
