<?php

namespace App\Models;

use App\Models\Superuser\Menu;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['code', 'name', 'department_id', 'level', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }

    public function employees()
    {
        return $this->hasMany(Employee::class, 'position_id');
    }

    /**
     * Menu yang boleh diakses posisi ini + hak akses per menu (can_view,
     * can_create, can_edit, can_delete). Sama persis pola-nya dengan
     * Role::menus(), hanya beda sisi (position, bukan role) — kombinasi
     * hak akses tidak terikat 1 posisi tertentu, bebas dipakai ulang di
     * posisi manapun lewat tabel pivot position_has_menus.
     */
    public function menus()
    {
        return $this->belongsToMany(Menu::class, 'position_has_menus', 'position_id', 'menu_id')
            ->withPivot(['can_view', 'can_create', 'can_edit', 'can_delete'])
            ->withTimestamps();
    }
}
