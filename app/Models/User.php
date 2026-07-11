<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'employee_id',
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function isSuperUser(): bool
    {
        return $this->role?->slug === 'super_user';
    }

    // public function 

    /**
     * Ambil daftar menu yang boleh diakses user ini berdasarkan role-nya.
     * Super user otomatis dapat semua menu aktif.
     */
    public function getMenus()
    {
        if (!$this->role) {
            return collect();
        }

        if ($this->isSuperUser()) {
            return Menu::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')])
                ->get();
        }

        return $this->role->menus()
            ->where('menus.is_active', true)
            ->wherePivot('can_view', true)
            ->whereNull('menus.parent_id')
            ->orderBy('menus.order')
            ->get();
    }
}
