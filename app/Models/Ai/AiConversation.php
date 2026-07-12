<?php

namespace App\Models\Ai;

use App\Models\Superuser\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiConversation extends Model
{
    protected $fillable = ['user_id', 'ai_connection_id', 'title', 'summary', 'summarized_until'];

    protected $casts = [
        'summarized_until' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function connection(): BelongsTo
    {
        return $this->belongsTo(AiConnection::class, 'ai_connection_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(AiMessage::class)->orderBy('created_at');
    }

    public function actionLogs(): HasMany
    {
        return $this->hasMany(AiActionLog::class);
    }
}
