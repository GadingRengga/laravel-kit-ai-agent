<?php

namespace App\Services\AI\DTO;

final class ToolCallDTO
{
    public function __construct(
        public readonly string $id,       // id dari provider, dipakai buat balas tool response
        public readonly string $name,     // nama tool, mis. 'create_customer'
        public readonly array $arguments, // sudah di-decode dari JSON
    ) {
    }
}
