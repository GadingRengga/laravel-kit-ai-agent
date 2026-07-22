<?php

namespace App\Models\Ai;

use App\Models\Superuser\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiActionLog extends Model
{
    protected $fillable = [
        'ai_conversation_id',
        'ai_message_id',
        'user_id',
        'tool_name',
        'summary',
        'payload',
        'status',
        'created_model_type',
        'created_model_id',
        'failure_reason',
    ];

    protected $casts = [
        'payload' => 'array',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** AiMessage (role=assistant, tool_calls) yang memicu draft ini. */
    public function message(): BelongsTo
    {
        return $this->belongsTo(AiMessage::class, 'ai_message_id');
    }

    /**
     * Cari AiMessage tool_call yang berkaitan dengan action log ini.
     *
     * Prioritas: relasi langsung `ai_message_id` (akurat, lihat migration
     * 2026_07_22_000000). Kalau kosong — data lama dari sebelum kolom ini
     * ada — jatuh balik ke heuristik lama (tool_name + content null +
     * terbaru) supaya action log lama tetap bisa diproses.
     */
    public function resolveToolCallMessage(): ?AiMessage
    {
        return $this->message ?? AiMessage::where('ai_conversation_id', $this->ai_conversation_id)
            ->where('role', 'assistant')
            ->where('tool_name', $this->tool_name)
            ->whereNull('content')
            ->latest()
            ->first();
    }

    public function createdModel(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'created_model_type', 'created_model_id');
    }
}
