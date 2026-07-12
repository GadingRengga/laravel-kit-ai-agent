<?php



// use Illuminate\Contracts\Auth\MustVerifyEmail;
namespace App\Models\Superuser;

use App\Models\Employee;
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

    /**
     * Cek apakah user ini — lewat POSISI JABATAN-nya (Employee->Position),
     * BUKAN lewat Role sidebar — punya hak akses tertentu atas sebuah menu.
     * Dipakai untuk membatasi tool AI (lihat GenericModelTool::isAllowedFor())
     * supaya AI cuma boleh melakukan aksi yang memang diizinkan untuk posisi
     * jabatan user, mis. Departemen Marketing - Posisi Staff.
     *
     * Default AMAN: kalau super user → selalu true. Kalau user tidak punya
     * employee/posisi, atau posisinya belum di-setting akses untuk menu
     * tersebut → FALSE (deny by default, bukan allow by default).
     *
     * @param  string  $menuSlug  slug menu (kolom `menus.slug`) yang jadi acuan
     * @param  string  $ability   salah satu: can_view, can_create, can_edit, can_delete
     */
    public function hasMenuAbility(string $menuSlug, string $ability = 'can_view'): bool
    {
        if ($this->isSuperUser()) {
            return true;
        }

        if (! in_array($ability, ['can_view', 'can_create', 'can_edit', 'can_delete'], true)) {
            throw new \InvalidArgumentException("Ability [{$ability}] tidak dikenal.");
        }

        $position = $this->employee?->position;

        if (! $position) {
            return false;
        }

        return $position->menus()
            ->where('menus.slug', $menuSlug)
            ->wherePivot($ability, true)
            ->exists();
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
