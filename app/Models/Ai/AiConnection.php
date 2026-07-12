<?php

namespace App\Models\Ai;

use App\Models\Superuser\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiConnection extends Model
{
    protected $fillable = [
        'user_id',
        'ai_provider_id',
        'api_key',
        'default_model',
        'is_active',
        'last_verified_at',
    ];

    protected $casts = [
        // Laravel otomatis encrypt/decrypt tiap kali disimpan/dibaca.
        // API key TIDAK PERNAH tersimpan plain di DB dan TIDAK PERNAH
        // di-append ke $hidden karena default-nya sudah aman via cast ini,
        // tapi tetap kita hidden-kan untuk jaga-jaga kalau model ke-serialize ke JSON.
        'api_key'          => 'encrypted',
        'is_active'        => 'boolean',
        'last_verified_at' => 'datetime',
    ];

    protected $hidden = ['api_key'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }

    public function resolvedModel(): string
    {
        return $this->default_model ?: $this->provider->default_model;
    }
}
