<?php

namespace App\Services\AI;

use App\Models\Superuser\User;
use App\Services\AI\Contracts\AiToolInterface;
use App\Services\AI\Tools\GenericModelTool;
use InvalidArgumentException;

class AiToolRegistry
{
    /** @var array<string, AiToolInterface> */
    private array $tools = [];

    public function register(AiToolInterface $tool): static
    {
        $this->tools[$tool->name()] = $tool;

        return $this;
    }

    public function get(string $name): AiToolInterface
    {
        return $this->tools[$name]
            ?? throw new InvalidArgumentException("AI tool [{$name}] tidak terdaftar.");
    }

    public function has(string $name): bool
    {
        return isset($this->tools[$name]);
    }

    /**
     * Ambil subset tool berdasarkan nama — dipakai supaya AiChatService cuma
     * kirim tools yang relevan dengan context halaman (mis. saat user di
     * halaman Quotation, gak perlu kirim skema create_customer). Ini bagian
     * dari strategi hemat token.
     *
     * @param  string[]  $names
     */
    public function only(array $names): array
    {
        return array_values(array_filter(
            $this->tools,
            fn (AiToolInterface $tool) => in_array($tool->name(), $names, true)
        ));
    }

    /** @return AiToolInterface[] */
    public function all(): array
    {
        return array_values($this->tools);
    }

    /**
     * Subset tool yang boleh "dilihat" AI untuk USER INI — menyaring
     * berdasarkan hak akses menu posisi jabatannya (lihat
     * User::hasMenuAbility() & GenericModelTool::isAllowedFor()).
     *
     * Ini lapisan filter di sisi "apa yang ditawarkan ke AI", bukan
     * pengganti authorize() di dalam tool itu sendiri — dua-duanya sengaja
     * dipertahankan (defense-in-depth): kalaupun somehow tool yang tidak
     * diizinkan lolos sampai sini, toDraft()/confirm() tetap menolaknya.
     *
     * Tool custom (bukan GenericModelTool, tidak declare menu+ability)
     * TIDAK ikut difilter di sini — biarkan authorize() masing-masing tool
     * yang menjaga, supaya perilaku lama tetap jalan seperti biasa.
     *
     * @param  AiToolInterface[]|null  $subset  null = mulai dari semua tool terdaftar
     */
    public function allowedFor(User $user, ?array $subset = null): array
    {
        $tools = $subset ?? $this->all();

        return array_values(array_filter(
            $tools,
            fn (AiToolInterface $tool) => ! $tool instanceof GenericModelTool || $tool->isAllowedFor($user)
        ));
    }

    /**
     * Format JSON Schema siap kirim ke OpenAI "tools" param.
     *
     * @param  AiToolInterface[]|null  $subset  null = pakai semua tool terdaftar
     */
    public function toSchemaArray(?array $subset = null): array
    {
        $tools = $subset ?? $this->all();

        return array_map(fn (AiToolInterface $tool) => [
            'type' => 'function',
            'function' => [
                'name'        => $tool->name(),
                'description' => $tool->description(),
                'parameters'  => $tool->schema(),
            ],
        ], $tools);
    }
}
