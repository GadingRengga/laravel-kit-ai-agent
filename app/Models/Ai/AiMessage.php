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
        'tool_call_id',
        'prompt_tokens',
        'completion_tokens',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    /** Format siap kirim ke OpenAI Chat Completions API. */
    public function toApiMessage(): array
    {
        $msg = ['role' => $this->role, 'content' => $this->content];

        if ($this->role === 'tool') {
            $msg['tool_call_id'] = $this->tool_call_id;
        }

        return $msg;
    }
}
