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
*/

return [

    [
        // Nama function, dikirim ke API sebagai nama tool. snake_case.
        'name' => 'create_customer',

        // Model Eloquent tujuan. Cukup tulis nama class-nya (string),
        // TIDAK perlu class ini sudah pasti ada saat file config di-load —
        // baru dicek saat tool ini benar-benar dipanggil.
        'model' => \App\Models\Customer::class,

        // Opsional: nama Gate/Policy ability yang dicek sebelum draft & confirm.
        // Kosongkan (hapus baris ini) kalau belum ada Policy utk model ini.
        'ability' => 'create',

        // Ini yang "mengajari" AI kapan tool ini relevan — tulis jelas & singkat.
        'description' => 'Membuat data customer baru. Panggil hanya kalau user '
            .'secara eksplisit minta dibuatkan customer/pelanggan baru dan sudah '
            .'menyebutkan minimal nama customer-nya.',

        // Template ringkasan yang tampil di kartu konfirmasi.
        // :field_name akan diganti otomatis dari nilai yang sudah divalidasi.
        'summary_template' => 'Buat customer baru: **:name**',

        // Field yang boleh diisi AI. type: string|number|boolean.
        'fields' => [
            'name' => [
                'type' => 'string',
                'required' => true,
                'description' => 'Nama customer atau perusahaan',
            ],
            'email' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Alamat email customer, kosongkan jika tidak disebutkan',
            ],
            'phone' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Nomor telepon/WhatsApp customer',
            ],
            'company' => [
                'type' => 'string',
                'required' => false,
                'description' => 'Nama perusahaan, jika berbeda dari nama customer',
            ],
        ],

        // Opsional: field yang otomatis diisi id user yang sedang login
        // (menggantikan 'created_by' => $user->id di Action class lama).
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
    //     'ability' => 'create',
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
