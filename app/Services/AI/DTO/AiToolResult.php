<?php

namespace App\Services\AI\DTO;

final class AiToolResult
{
    /**
     * @param string $summary      Teks singkat buat ditampilkan, mis. "Buat User baru: John"
     * @param array  $payload      Argumen yang sudah tervalidasi
     * @param string $modelClass   App\Models\Customer::class
     * @param bool   $isDirect     true = tool langsung dieksekusi (READ), tanpa draft/confirm
     * @param mixed  $directResult Hasil langsung dari eksekusi (misal collection data untuk READ)
     */
    private function __construct(
        public readonly string $summary,
        public readonly array $payload,
        public readonly string $modelClass,
        public readonly bool $isDirect = false,
        public readonly mixed $directResult = null,
    ) {}

    /**
     * Untuk operasi CREATE / UPDATE / DELETE — butuh draft + confirm.
     */
    public static function draft(string $modelClass, array $payload, string $summary): self
    {
        return new self($summary, $payload, $modelClass);
    }

    /**
     * Untuk operasi READ — hasil langsung diberikan, tanpa perlu draft/confirm.
     */
    public static function direct(string $modelClass, array $payload, string $summary, mixed $result): self
    {
        return new self($summary, $payload, $modelClass, isDirect: true, directResult: $result);
    }
}
