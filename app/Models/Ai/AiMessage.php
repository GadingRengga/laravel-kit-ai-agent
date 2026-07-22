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

    /** AiActionLog yang dibuat dari pesan tool_call ini (kalau ada). */
    public function actionLog(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(AiActionLog::class, 'ai_message_id');
    }

    /** True kalau pesan ini adalah pemanggilan tool oleh assistant (bukan teks biasa). */
    public function isToolCall(): bool
    {
        return $this->role === 'assistant' && ! empty($this->tool_name) && is_null($this->content);
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
            // Gemini membutuhkan name fungsi di functionResponse,
            // jadi kita sertakan tool_name juga di sini
            if ($this->tool_name) {
                $msg['tool_name'] = $this->tool_name;
            }
        }

        return $msg;
    }
}
