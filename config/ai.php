<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Batas hemat token
    |--------------------------------------------------------------------------
    | Angka-angka ini yang paling menentukan biaya API kamu. Ubah di sini,
    | jangan hardcode di service.
    */
    'history_window'      => env('AI_HISTORY_WINDOW', 8),      // jumlah pesan mentah terakhir yang dikirim utuh
    'summarize_threshold'  => env('AI_SUMMARIZE_THRESHOLD', 20), // setelah sekian pesan, ringkas otomatis
    'max_response_tokens' => env('AI_MAX_RESPONSE_TOKENS', 500),

    /*
    |--------------------------------------------------------------------------
    | System prompt dasar
    |--------------------------------------------------------------------------
    | Sengaja pendek. Detail "kapan tool dipakai" ada di description() masing-
    | masing tool, bukan di sini — supaya system prompt gak membengkak tiap
    | kali nambah tool baru.
    */
    'system_prompt' => <<<'PROMPT'
    Kamu adalah asisten internal aplikasi bisnis. Bantu user mengelola data
    lewat percakapan natural bahasa Indonesia.

    PENTING soal HAK AKSES: kemampuanmu untuk user ini DIBATASI oleh
    permission mereka. Daftar aksi yang benar-benar bisa kamu lakukan untuk
    user ini diberikan lewat system message terpisah berlabel
    "KEMAMPUAN AKTIF UNTUK USER INI". SELALU rujuk daftar itu — jangan pernah
    mengarang atau berasumsi user bisa membuat data tertentu (mis. customer,
    quotation, order) kecuali aksinya memang ada di daftar tersebut. Kalau
    user bertanya "saya bisa akses apa saja / apa yang bisa kamu bantu", jawab
    HANYA berdasarkan daftar itu; kalau daftarnya kosong, katakan jujur bahwa
    mereka belum punya akses dan sarankan menghubungi admin.

    Jika user memberi info yang cukup untuk membuat sebuah data (dan aksinya
    ada di daftar kemampuan), panggil tool yang sesuai. Jika info belum cukup,
    tanyakan dulu sebelum memanggil tool. Jangan mengarang nilai yang tidak
    disebutkan user.

    Kalau user bertanya hal di luar pengelolaan data — basa-basi, sapaan, atau
    pertanyaan soal dirimu sendiri (mis. "kamu AI apa?", "ini aplikasi apa?")
    — jawab pertanyaannya secara langsung dan jujur dulu. JANGAN mengulang
    daftar fitur/menu kecuali user memang menanyakan apa yang bisa kamu bantu.
    PROMPT,

    /*
    |--------------------------------------------------------------------------
    | Provider clients
    |--------------------------------------------------------------------------
    */
    'providers' => [
        'openai' => [
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
            'timeout'  => env('OPENAI_TIMEOUT', 30),
        ],
        'gemini' => [
            'base_url' => 'https://generativelanguage.googleapis.com/v1beta',
            'timeout'  => 30,
        ],
    ],

];
