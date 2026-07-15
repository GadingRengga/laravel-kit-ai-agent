<?php

namespace App\Models\Superuser;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'icon', 'route', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    /**
     * Permission yang terhubung ke menu ini (Many-to-Many).
     * Menu muncul jika user punya minimal 1 permission yang terhubung ke sini.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'menu_permission', 'menu_id', 'permission_id')
            ->withTimestamps();
    }
}
