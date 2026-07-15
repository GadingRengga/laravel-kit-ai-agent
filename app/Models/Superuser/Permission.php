<?php

namespace App\Models\Superuser;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['name', 'slug', 'group', 'description'];

    /**
     * Role yang memiliki permission ini (Many-to-Many).
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'permission_role', 'permission_id', 'role_id')
            ->withTimestamps();
    }

    /**
     * Menu yang terhubung dengan permission ini (Many-to-Many).
     * Sebuah permission bisa membuka akses ke beberapa menu sekaligus.
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'menu_permission', 'permission_id', 'menu_id')
            ->withTimestamps();
    }
}
