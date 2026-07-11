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
        Kamu adalah asisten internal aplikasi bisnis. Bantu user membuat data
        (customer, quotation, order, dll) lewat percakapan natural bahasa Indonesia.
        Jika user memberi info yang cukup untuk membuat sebuah data, panggil tool
        yang sesuai. Jika info belum cukup, tanyakan dulu sebelum memanggil tool.
        Jangan mengarang nilai yang tidak disebutkan user.
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
