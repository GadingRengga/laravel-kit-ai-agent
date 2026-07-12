<?php

namespace App\Models\Superuser;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relasi manual (tanpa FK di DB, tapi tetap bisa dipakai Eloquent)
    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'role_has_permissions', 'role_id', 'permission_id');
    }

    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'role_has_menus', 'role_id', 'menu_id')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete']);
    }

    public function isSuperUser(): bool
    {
        return $this->slug === 'super_user';
    }
}
