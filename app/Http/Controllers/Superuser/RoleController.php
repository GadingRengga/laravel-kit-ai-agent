<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\Permission;
use App\Models\Superuser\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Halaman daftar role.
     */
    public function index(): View
    {
        return view('pages.superuser.role.index', [
            'roles' => $this->roles(),
        ]);
    }

    /**
     * Form create role di modal.
     */
    public function create(): View
    {
        return view('pages.superuser.role.partials._form', [
            'role' => new Role(['is_active' => true]),
            'permissions' => $this->allPermissions(),
            'selectedPermissionIds' => [],
        ]);
    }

    /**
     * Form edit role di modal.
     */
    public function edit(Request $request): View
    {
        $id = $request->input('data');
        $role = Role::with('permissions')->findOrFail($id);

        return view('pages.superuser.role.partials._form', [
            'role' => $role,
            'permissions' => $this->allPermissions(),
            'selectedPermissionIds' => $role->permissions->pluck('id')->all(),
        ]);
    }

    /**
     * Simpan role baru.
     */
    public function store(Request $request): View
    {
        return $this->save($request, null);
    }

    /**
     * Update role.
     */
    public function update(Request $request): View
    {
        return $this->save($request, $request->id);
    }

    /**
     * Hapus role.
     */
    public function destroy(Request $request): View
    {
        $id = $request->input('data');

        if (!is_numeric($id)) {
            return $this->renderPanel(error: 'ID role tidak valid.');
        }

        $role = Role::find((int) $id);

        if (!$role) {
            return $this->renderPanel(error: 'Role tidak ditemukan atau sudah dihapus.');
        }

        if ($role->slug === 'super_user') {
            return $this->renderPanel(error: 'Role Super User tidak bisa dihapus.');
        }

        DB::beginTransaction();

        try {
            $roleName = $role->name;

            $role->users()->detach();
            $role->permissions()->detach();
            $role->delete();

            DB::commit();

            return $this->renderPanel(success: "Role \"{$roleName}\" berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(error: 'Terjadi kesalahan saat menghapus role. Coba lagi nanti.');
        }
    }

    protected function save(Request $request, ?int $id = null): View
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . ($id ?: 'NULL') . ',id',
            'description' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'permission_ids' => 'nullable|array',
            'permission_ids.*' => 'exists:permissions,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active');

        $permissionIds = $data['permission_ids'] ?? [];
        unset($data['permission_ids']);

        DB::beginTransaction();

        try {
            $role = Role::updateOrCreate(
                ['id' => $id],
                $data
            );

            // Super User memiliki seluruh permission secara otomatis
            if ($role->slug !== 'super_user') {
                $role->permissions()->sync($permissionIds);
            }

            DB::commit();

            return $this->renderPanel(
                success: $id
                    ? 'Role berhasil diperbarui.'
                    : 'Role baru berhasil ditambahkan.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->renderPanel(
                error: $e->getMessage()
            );
        }
    }

    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.role.partials._panel', [
            'roles' => $this->roles(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    protected function roles()
    {
        return Role::withCount('users', 'permissions')
            ->orderBy('name')
            ->get();
    }

    protected function allPermissions()
    {
        return Permission::orderBy('group')->orderBy('name')->get();
    }
}
