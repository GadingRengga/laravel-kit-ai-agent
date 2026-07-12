<?php

namespace App\Models\Ai;

use App\Models\Superuser\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiActionLog extends Model
{
    protected $fillable = [
        'ai_conversation_id',
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

    public function createdModel(): \Illuminate\Database\Eloquent\Relations\MorphTo
    {
        return $this->morphTo(__FUNCTION__, 'created_model_type', 'created_model_id');
    }
}
