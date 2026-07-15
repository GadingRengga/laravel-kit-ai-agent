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
| Batasan akses: 'menu' + 'ability'
|--------------------------------------------------------------------------
| 'menu'    slug menu (kolom `menus.slug`, lihat Manajemen Menu) yang jadi
|           "rumah" fitur ini. Isi dengan slug menu sidebar yang paling
|           relevan — mis. tool bikin data quotation → slug menu Quotation.
| 'ability' salah satu: can_view, can_create, can_edit, can_delete — HARUS
|           persis salah satu dari 4 ini (sama dengan kolom pivot
|           position_has_menus / role_has_menus), bukan nama Gate ability
|           bebas seperti sebelumnya.
|
| Kombinasi keduanya dicek ke matrix "Akses Menu per Posisi" milik user
| (lihat App\Models\Superuser\User::hasMenuAbility() dan
| App\Services\AI\Tools\GenericModelTool::isAllowedFor()): kalau posisi
| jabatan user tidak dicentang `ability` untuk `menu` tsb, AI tidak akan
| menawarkan/menjalankan tool ini sama sekali untuk user itu.
|
| Kosongkan salah satu ATAU keduanya kalau tool ini sengaja mau dibuka
| untuk SEMUA user yang login, tanpa perlu setting akses per posisi dulu.
*/

return [
    [
        'name' => 'create_menu',
        'model' => '\App\Models\Menu::class',
        'menu' => 'menu', // TODO: ganti ke slug menu sidebar asli yang menaungi fitur pemesanan menu customer
        'ability' => 'can_create',
        'description' => 'Membuat menu baru.',
        'summary_template' => 'Buat menu baru untuk **:customer_name** senilai **:total**',
        'fields' => [
            'namep' => ['name' => 'string', 'required' => true, 'description' => 'Nama menu sidebar'],
        ],
        'stamp_user_as' => 'created_by',
    ],


    /*
    |--------------------------------------------------------------------------
    | Contoh nambah tool baru — tinggal copy-paste blok di atas & sesuaikan.
    | Tidak perlu bikin file PHP baru sama sekali.
    |--------------------------------------------------------------------------
    */
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
