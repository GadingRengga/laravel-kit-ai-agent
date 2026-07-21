<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiMessage extends Model
{
    protected $fillable = [
        'ai_conversation_id',
        'role',
        'content',
        'tool_name',
        'tool_arguments',
        'tool_call_id',
        'prompt_tokens',
        'completion_tokens',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'tool_arguments' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /** Format siap kirim ke OpenAI Chat Completions API. */
    public function toApiMessage(): array
    {
        $msg = ['role' => $this->role, 'content' => $this->content];

        if ($this->role === 'assistant' && $this->tool_name) {
            // Sertakan tool_calls supaya provider AI bisa menghubungkan
            // assistant message ini dengan tool response setelahnya.
            $msg['tool_calls'] = [
                [
                    'id' => $this->tool_call_id ?? ('call_' . $this->id),
                    'type' => 'function',
                    'function' => [
                        'name' => $this->tool_name,
                        'arguments' => is_array($this->tool_arguments)
                            ? json_encode($this->tool_arguments)
                            : ($this->tool_arguments ?? '{}'),
                    ],
                ],
            ];
        }

        if ($this->role === 'tool') {
            $msg['tool_call_id'] = $this->tool_call_id;
        }

        return $msg;
    }
}
