<?php

namespace App\Services\AI\Contracts;

use App\Models\Ai\AiConnection;
use App\Services\AI\DTO\AiChatResponse;

interface AiProviderInterface
{
    /**
     * Kirim percakapan + daftar tools ke provider, terima balasan yang sudah
     * dinormalisasi jadi AiChatResponse (terlepas dari format asli tiap provider).
     *
     * @param  array<int, array{role:string, content:?string, tool_call_id?:string}>  $messages
     * @param  array<int, array>  $toolSchemas  hasil dari AiToolRegistry::toSchemaArray()
     */
    public function chat(AiConnection $connection, array $messages, array $toolSchemas): AiChatResponse;
}
