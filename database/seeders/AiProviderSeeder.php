<?php

namespace Database\Seeders;

use App\Models\Ai\AiProvider;
use Illuminate\Database\Seeder;

/*
|--------------------------------------------------------------------------
| BUGFIX: dropdown provider kosong
|--------------------------------------------------------------------------
| Dropdown "Provider" di modal koneksi (widget maupun halaman chat) diisi
| dari query AiProvider::where('is_active', true)->get() — query-nya sudah
| benar, tapi TIDAK ADA seeder di paket kode ini yang mengisi tabel
| ai_providers. Kalau tabel ini kosong, dropdown-nya pasti kosong juga,
| apa pun yang kamu lakukan di sisi Blade/JS.
|
| `code` di sini HARUS persis sama dengan:
|   - key di config/ai.php > providers (openai / gemini)
|   - key di config/ai_models.php (openai / gemini)
|   - $code = $connection->provider?->code yang dicek di AiProviderManager
| kalau salah satu beda, AiProviderManager tidak akan bisa resolve provider
| class yang tepat walau koneksinya sudah tersimpan.
*/
class AiProviderSeeder extends Seeder
{
    public function run(): void
    {
        $providers = [
            [
                'code'          => 'openai',
                'label'         => 'ChatGPT (OpenAI)',
                'default_model' => 'gpt-4.1-mini',
                'is_active'     => true,
            ],
            [
                'code'          => 'gemini',
                'label'         => 'Gemini (Google)',
                'default_model' => 'gemini-3.5-flash',
                'is_active'     => true,
            ],
        ];

        foreach ($providers as $provider) {
            AiProvider::updateOrCreate(
                ['code' => $provider['code']],
                $provider,
            );
        }
    }
}
