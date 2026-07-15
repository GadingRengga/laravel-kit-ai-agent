<?php

namespace App\Models\Superuser;

use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
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

    /**
     * Seorang user bisa memiliki banyak role (Many-to-Many).
     * Pivot: role_user
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_user', 'user_id', 'role_id')
            ->withTimestamps();
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    /**
     * Cek apakah user memiliki role super_user.
     */
    public function isSuperUser(): bool
    {
        return $this->roles()->where('slug', 'super_user')->exists();
    }

    /**
     * Ambil seluruh permission yang dimiliki user — gabungan dari semua role-nya.
     * Super user otomatis memiliki semua permission yang ada.
     *
     * @return Collection<Permission>
     */
    public function getAllPermissions(): Collection
    {
        if ($this->isSuperUser()) {
            return Permission::all();
        }

        $roleIds = $this->roles()->pluck('roles.id');

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return Permission::whereHas('roles', fn($q) => $q->whereIn('roles.id', $roleIds))->get();
    }

    /**
     * Cek apakah user memiliki permission tertentu (berdasarkan slug permission).
     *
     * @param  string  $permissionSlug  slug permission, mis. "menu.create", "employee.view"
     */
    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        return $this->roles()
            ->whereHas('permissions', fn($q) => $q->where('permissions.slug', $permissionSlug))
            ->exists();
    }

    /**
     * Cek apakah user memiliki akses ke suatu menu + ability tertentu.
     * MENU mengikuti PERMISSION — menu muncul jika user punya minimal 1 permission
     * yang terhubung ke menu tersebut.
     *
     * Dipakai untuk membatasi tool AI (lihat GenericModelTool::isAllowedFor()).
     *
     * @param  string  $menuSlug  slug menu (kolom `menus.slug`)
     * @param  string  $ability   tidak dipakai lagi karena menu mengikuti permission, dipertahankan untuk kompatibilitas
     */
    public function hasMenuAbility(string $menuSlug, string $ability = 'can_view'): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        // Cek apakah user punya minimal 1 permission yang terhubung ke menu ini
        $permissionIds = $this->getAllPermissions()->pluck('id');

        return Menu::where('slug', $menuSlug)
            ->whereHas('permissions', fn($q) => $q->whereIn('permissions.id', $permissionIds))
            ->exists();
    }

    public function getMenus(): Collection
    {
        if ($this->isSuperUser()) {
            return Menu::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('order')
                ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')])
                ->get();
        }

        $permissionIds = $this->getAllPermissions()->pluck('id');

        if ($permissionIds->isEmpty()) {
            return collect();
        }

        // Ambil menu parent yang memiliki permission terkait
        return Menu::where('is_active', true)
            ->whereNull('parent_id')
            ->whereHas('permissions', fn($q) => $q->whereIn('permissions.id', $permissionIds))
            ->orderBy('order')
            ->with([
                'children' => fn($q) => $q->where('is_active', true)
                    ->whereHas('permissions', fn($q2) => $q2->whereIn('permissions.id', $permissionIds))
                    ->orderBy('order')
            ])
            ->get();
    }
}
