<?php

namespace App\Models\Ai;

use Illuminate\Database\Eloquent\Model;

class AiProvider extends Model
{
    protected $fillable = ['code', 'label', 'default_model', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function connections()
    {
        return $this->hasMany(AiConnection::class);
    }
}
