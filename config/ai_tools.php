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

    /*
    |--------------------------------------------------------------------------
    | Tool CRUD — User Management
    |--------------------------------------------------------------------------
    | Setiap tool menggunakan permission slug untuk kontrol akses.
    | GenericModelTool sekarang mendukung operation: create, read, update, delete.
    | Cukup tambah 1 array per tool — tidak perlu bikin class baru.
    */

    // CATATAN SCOPE: tool User di bawah ini SENGAJA tidak diisi 'scope' —
    // dianggap global (admin/superuser memang boleh kelola semua user).
    // Untuk entity bisnis (quotation/order/project/dll) yang harus
    // terisolasi per user/departemen, WAJIB isi 'scope' => 'own_department'
    // atau 'own_records' (lihat GenericModelTool::applyScope()) — kalau
    // lupa, itu jadi celah IDOR: user bisa baca/analisis data user lain.

    // ── CREATE USER ─────────────────────────────────────────────────────────
    [
        'name'        => 'create_user',
        'model'       => \App\Models\Superuser\User::class,
        'operation'   => 'create',
        'permission'  => 'user.create',
        'description' => 'Membuat user baru. Parameter: nama, email, username (opsional), password, role, is_active (opsional).',
        'summary_template' => 'Buat User baru: **:name**',
        'fields' => [
            'name'      => ['type' => 'string',  'description' => 'Nama lengkap user', 'required' => true],
            'email'     => ['type' => 'string',  'format' => 'email', 'description' => 'Email user', 'required' => true],
            'username'  => ['type' => 'string',  'description' => 'Username untuk login (opsional)'],
            'password'  => ['type' => 'string',  'description' => 'Password untuk login (minimal 8 karakter)', 'required' => true],
            'role'      => ['type' => 'string',  'description' => 'Nama role (misal: Staff, Admin) atau ID role', 'required' => true],
            'is_active' => ['type' => 'boolean', 'description' => 'Status aktif user (default: true)'],
        ],
        'stamp_user_as' => 'created_by',
    ],

    // ── READ USER ───────────────────────────────────────────────────────────
    [
        'name'        => 'read_user',
        'model'       => \App\Models\Superuser\User::class,
        'operation'   => 'read',
        'permission'  => 'user.view',
        'description' => 'Mencari atau menampilkan data user. Bisa menampilkan relasi seperti roles, employee. Parameter: id (detail), search (kata kunci), role (filter role), is_active (filter status), with (relasi tambahan), limit (batas jumlah).',
        'summary_template' => 'Menampilkan data User',
        // Relasi yang selalu di-load
        'with' => ['roles', 'employee'],
        // Whitelist relasi yang diizinkan — AI tidak bisa load relasi di luar ini
        'allowed_with' => ['roles', 'employee', 'createdBy'],
        // Filter relasi generic — AI bisa filter berdasarkan nama role
        'relation_filters' => [
            'role' => ['relation' => 'roles', 'field' => 'name'],
        ],
        'fields' => [
            'id'        => ['type' => 'integer', 'description' => 'ID user spesifik (gunakan untuk melihat 1 data detail)'],
            'search'    => ['type' => 'string',  'description' => 'Kata kunci untuk mencari berdasarkan nama, email, atau username'],
            'role'      => ['type' => 'string',  'description' => 'Filter berdasarkan nama role tertentu (misal: Staff, Admin)'],
            'is_active' => ['type' => 'boolean', 'description' => 'Filter status aktif (true=aktif, false=tidak aktif)'],
            'with'      => ['type' => 'array',   'description' => 'Relasi tambahan yang ingin ditampilkan. Pilihan: ["roles", "employee", "createdBy"]'],
            'limit'     => ['type' => 'integer', 'description' => 'Membatasi jumlah data per halaman (default: 10)'],
            'page'      => ['type' => 'integer', 'description' => 'Halaman ke berapa (default: 1). Gunakan untuk mengambil halaman berikutnya'],
        ],
    ],

    // ── UPDATE USER ─────────────────────────────────────────────────────────
    [
        'name'        => 'update_user',
        'model'       => \App\Models\Superuser\User::class,
        'operation'   => 'update',
        'permission'  => 'user.edit',
        'description' => 'Mengubah data user yang sudah ada. Parameter wajib: id. Parameter opsional yang bisa diubah: name, email, username, password, is_active.',
        'summary_template' => 'Update User ID **:id**',
        'fields' => [
            'id'        => ['type' => 'integer', 'description' => 'ID user yang akan diubah', 'required' => true],
            'name'      => ['type' => 'string',  'description' => 'Nama baru user (opsional)'],
            'email'     => ['type' => 'string',  'format' => 'email', 'description' => 'Email baru (opsional)'],
            'username'  => ['type' => 'string',  'description' => 'Username baru (opsional)'],
            'password'  => ['type' => 'string',  'description' => 'Password baru, minimal 8 karakter (opsional)'],
            'is_active' => ['type' => 'boolean', 'description' => 'Status aktif baru (opsional)'],
        ],
    ],

    // ── DELETE USER ─────────────────────────────────────────────────────────
    [
        'name'        => 'delete_user',
        'model'       => \App\Models\Superuser\User::class,
        'operation'   => 'delete',
        'permission'  => 'user.delete',
        'description' => 'Menghapus user berdasarkan ID. Parameter: id (wajib).',
        'summary_template' => 'Hapus User ID **:id**',
        'fields' => [
            'id' => ['type' => 'integer', 'description' => 'ID user yang akan dihapus', 'required' => true],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Template nambah tool CRUD untuk entity lain (contoh: Order)
    |--------------------------------------------------------------------------
    | Cukup copy-paste blok di bawah, sesuaikan model, permission, field.
    | Tidak perlu bikin class PHP baru.
    |
    | Pastikan permission slug-nya sudah ada di database (jalankan PermissionSeeder).
    |
    | Contoh untuk Order:
    | [
    |     'name'        => 'create_order',
    |     'model'       => \App\Models\Order::class,
    |     'operation'   => 'create',
    |     'permission'  => 'order.create',
    |     'description' => 'Membuat order baru...',
    |     'fields' => [...],
    |     'stamp_user_as' => 'created_by',
    | ],
    |
    | Untuk READ:
    | [
    |     'name'        => 'read_order',
    |     'model'       => \App\Models\Order::class,
    |     'operation'   => 'read',
    |     'permission'  => 'order.view',
    |     'description' => 'Mencari atau menampilkan data order...',
    |     'fields' => [...],
    | ],
    |
    | Untuk UPDATE:
    | [
    |     'name'        => 'update_order',
    |     'model'       => \App\Models\Order::class,
    |     'operation'   => 'update',
    |     'permission'  => 'order.edit',
    |     'description' => 'Mengubah data order...',
    |     'fields' => [...],
    | ],
    |
    | Untuk DELETE:
    | [
    |     'name'        => 'delete_order',
    |     'model'       => \App\Models\Order::class,
    |     'operation'   => 'delete',
    |     'permission'  => 'order.delete',
    |     'description' => 'Menghapus data order...',
    |     'fields' => [...],
    | ],
    |
    | Untuk ANALISIS (agregasi — total/rata-rata/jumlah, opsional per kelompok):
    | [
    |     'name'        => 'analyze_order',
    |     'model'       => \App\Models\Order::class,
    |     'operation'   => 'aggregate',
    |     'permission'  => 'order.view',
    |     'scope'       => 'own_department', // WAJIB diisi utk entity bisnis
    |     'description' => 'Analisis data order: total nilai, jumlah, rata-rata, '
    |         . 'bisa dikelompokkan per status/customer/dll.',
    |     'date_column'    => 'created_at',        // dipakai utk date_from/date_to
    |     'aggregatable'   => ['total_value'],      // whitelist kolom yg boleh di-sum/avg
    |     'groupable'      => ['status'],           // whitelist kolom yg boleh di-group_by
    |     'numeric_filters' => ['total_value'],     // opsional: filter total_value_min/max
    |     'fields' => [
    |         // field metric/column/group_by + date_from/date_to/xxx_min/xxx_max
    |         // OTOMATIS di-inject oleh GenericModelTool::schema() berdasarkan
    |         // 'date_column'/'numeric_filters'/'aggregatable'/'groupable' di atas —
    |         // tidak perlu ditulis manual di sini.
    |     ],
    | ],
    */

];
