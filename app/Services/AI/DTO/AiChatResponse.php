<?php

namespace App\Services\AI\DTO;

final class AiChatResponse
{
    /** @param  ToolCallDTO[]  $toolCalls */
    public function __construct(
        public readonly ?string $content,
        public readonly array $toolCalls,
        public readonly int $promptTokens,
        public readonly int $completionTokens,
    ) {
    }

    public function hasToolCalls(): bool
    {
        return count($this->toolCalls) > 0;
    }
}
