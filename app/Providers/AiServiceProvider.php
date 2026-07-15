<?php

namespace App\Providers;

use App\Services\AI\AiProviderManager;
use App\Services\AI\AiToolRegistry;
use App\Services\AI\Tools\GenericModelTool;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * Provider terpisah khusus modul AI supaya AppServiceProvider utama gak penuh.
 * Tinggal tambahkan ke bootstrap/providers.php (Laravel 11):
 *   App\Providers\AiServiceProvider::class,
 */
class AiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Dulu di sini ada binding statis:
        //   $this->app->bind(AiProviderInterface::class, OpenAiProvider::class);
        // Itu bikin SEMUA koneksi (apapun provider-nya di ai_providers/AiConnection)
        // selalu kepanggil OpenAiProvider. Sekarang provider dipilih dinamis per
        // AiConnection lewat AiProviderManager::resolve() — lihat class itu untuk
        // daftar provider yang didukung (openai, gemini, dst).
        $this->app->singleton(AiProviderManager::class);

        $this->app->singleton(AiToolRegistry::class, function ($app) {
            $registry = new AiToolRegistry();

            // ── Tool dinamis dari config/ai_tools.php ──────────────────────
            // Tambah "function" baru buat AI = tambah 1 array di config,
            // TIDAK perlu bikin class PHP baru. Kalau salah satu entry
            // config-nya rusak/typo, entry itu SKIP (tidak bikin seluruh
            // chat down) — dicatat ke log supaya kamu tahu perlu dibetulkan.
            foreach (config('ai_tools', []) as $definition) {
                try {
                    $registry->register(new GenericModelTool($definition));
                } catch (\Throwable $e) {
                    Log::warning('[AI] Gagal mendaftarkan tool dari config/ai_tools.php, dilewati.', [
                        'tool' => $definition['name'] ?? '(nama tidak diketahui)',
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // ── Tool custom (opsional) ──────────────────────────────────────
            // Kalau ada business logic yang lebih rumit dari sekadar
            // "Model::create()" (generate nomor invoice, kirim notifikasi,
            // dsb), tetap bisa daftarkan Tool class manual di sini seperti
            // sebelumnya — dibungkus try/catch juga supaya kalau class-nya
            // belum lengkap, TIDAK menjatuhkan seluruh fitur chat:
            try {
                $registry->register($app->make(\App\Services\AI\Tools\CreateUserTool::class));
            } catch (\Throwable $e) {
                Log::warning('[AI] Gagal mendaftarkan CreateUserTool, dilewati.', ['error' => $e->getMessage()]);
            }

            return $registry;
        });
    }

    public function boot(): void
    {
        // Batasi jumlah pesan chat per user per menit — jaga-jaga dari
        // penyalahgunaan yang bisa membengkakkan tagihan API user (BYOK,
        // tapi tetap beban server kita juga).
        RateLimiter::for('ai-chat', function ($request) {
            return Limit::perMinute(20)->by($request->user()?->id ?: $request->ip());
        });
    }
}
