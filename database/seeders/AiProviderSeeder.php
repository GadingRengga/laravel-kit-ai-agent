<?php

namespace Database\Seeders;

use App\Models\Ai\AiProvider;
use Illuminate\Database\Seeder;

class AiProviderSeeder extends Seeder
{
    public function run(): void
    {
        AiProvider::updateOrCreate(
            ['code' => 'openai'],
            [
                'label'         => 'ChatGPT (OpenAI)',
                'default_model' => 'gpt-4.1-mini', // cek model & harga terbaru di dashboard OpenAI-mu, ini gampang berubah
                'is_active'     => true,
            ]
        );

        AiProvider::updateOrCreate(
            ['code' => 'gemini'],
            [
                'label'         => 'Google Gemini',
                'default_model' => 'gemini-3.5-flash', // gemini-2.0-flash SUDAH DI-SHUTDOWN Google per 1 Juni 2026 — cek model aktif terbaru di ai.google.dev/gemini-api/docs/models
                'is_active'     => true,
            ]
        );
    }
}
