<?php

/*
|--------------------------------------------------------------------------
| Daftar model yang muncul di dropdown "Model" widget AI, per provider.
|--------------------------------------------------------------------------
| PENTING: model AI berubah SANGAT cepat (rilis baru tiap beberapa minggu,
| model lama sering di-deprecate/shutdown tanpa ampun). List di bawah ini
| akurat per Juli 2026 — cek ulang berkala sebelum production:
|
|   OpenAI : https://platform.openai.com/docs/models
|   Gemini : https://ai.google.dev/gemini-api/docs/models
|
| Format 'value' HARUS persis sama dengan model id yang diterima API
| (dipakai di field 'model' waktu request ke /chat/completions atau
| :generateContent). Urutan array = urutan tampil di dropdown.
*/

return [

    'openai' => [
        ['value' => 'gpt-5.5', 'label' => 'GPT-5.5 — paling pintar, paling mahal'],
        ['value' => 'gpt-4.1-mini', 'label' => 'GPT-4.1 mini — seimbang (direkomendasikan)'],
        ['value' => 'gpt-4o-mini', 'label' => 'GPT-4o mini — murah & cepat'],
    ],

    'gemini' => [
        ['value' => 'gemini-3.1-pro', 'label' => 'Gemini 3.1 Pro — paling pintar, paling mahal'],
        ['value' => 'gemini-3.5-flash', 'label' => 'Gemini 3.5 Flash — seimbang (direkomendasikan)'],
        ['value' => 'gemini-3.1-flash-lite', 'label' => 'Gemini 3.1 Flash Lite — murah & cepat'],
    ],

];
