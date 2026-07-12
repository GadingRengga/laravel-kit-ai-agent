<?php

namespace App\Models\Superuser;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['parent_id', 'name', 'slug', 'icon', 'route', 'order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id')->orderBy('order');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_has_menus', 'menu_id', 'role_id')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete']);
    }

    /**
     * Posisi jabatan yang diberi akses ke menu ini, lewat pivot
     * position_has_menus — pasangan dari Position::menus().
     */
    public function positions()
    {
        return $this->belongsToMany(\App\Models\Position::class, 'position_has_menus', 'menu_id', 'position_id')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete'])
            ->withTimestamps();
    }
}
