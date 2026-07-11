<?php

namespace App\Services\AI;

use App\Models\Ai\AiConnection;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\Providers\GeminiProvider;
use App\Services\AI\Providers\OpenAiProvider;
use Illuminate\Contracts\Container\Container;
use RuntimeException;

/**
 * Sebelumnya AiProviderInterface di-bind STATIS ke OpenAiProvider di
 * AiServiceProvider — artinya walau user connect ke provider lain, yang
 * kepanggil tetap OpenAiProvider. Class ini menggantikan pola itu: pilih
 * implementasi provider yang tepat berdasarkan `AiConnection->provider->code`,
 * di-resolve SETIAP kali dipanggil (bukan singleton per-app), karena satu
 * request/koneksi bisa aja provider-nya beda-beda.
 *
 * Nambah provider baru (misal 'openrouter', 'anthropic') = tinggal:
 *   1. Bikin class-nya, implements AiProviderInterface.
 *   2. Tambah 1 baris di $map di bawah.
 *   3. Tambah row di tabel ai_providers dengan `code` yang sama.
 */
class AiProviderManager
{
    /** @var array<string, class-string<AiProviderInterface>> */
    private array $map = [
        'openai' => OpenAiProvider::class,
        'gemini' => GeminiProvider::class,
    ];

    public function __construct(private readonly Container $app)
    {
    }

    public function resolve(AiConnection $connection): AiProviderInterface
    {
        $code = $connection->provider?->code;

        if (! $code || ! isset($this->map[$code])) {
            throw new RuntimeException(
                "Provider AI [{$code}] belum didukung. Cek AiProviderManager::\$map."
            );
        }

        return $this->app->make($this->map[$code]);
    }
}
