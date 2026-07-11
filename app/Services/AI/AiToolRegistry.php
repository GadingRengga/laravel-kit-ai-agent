<?php

namespace App\Services\AI;

use App\Services\AI\Contracts\AiToolInterface;
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
