<?php

namespace App\Models;

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
}
