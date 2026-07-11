<?php

namespace App\Services\AI\DTO;

final class AiToolResult
{
    private function __construct(
        public readonly string $summary,   // teks singkat buat ditampilkan di card, mis. "Customer baru: PT Maju Jaya"
        public readonly array $payload,     // argumen yang sudah tervalidasi, siap disimpan sbg ai_action_logs.payload
        public readonly string $modelClass, // App\Models\Customer::class
    ) {
    }

    public static function draft(string $modelClass, array $payload, string $summary): self
    {
        return new self($summary, $payload, $modelClass);
    }
}
