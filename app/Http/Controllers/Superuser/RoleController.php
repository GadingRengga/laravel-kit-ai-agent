<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\Permission;
use App\Models\Superuser\Role;
use Illuminate\Http\Request;
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
    public function destroy(int $id): View
    {
        $role = Role::findOrFail($id);

        if ($role->slug === 'super_user') {
            return $this->renderPanel(error: 'Role Super User tidak bisa dihapus.');
        }

        $role->users()->detach();
        $role->permissions()->detach();
        $role->delete();

        return $this->renderPanel(success: 'Role "' . $role->name . '" berhasil dihapus.');
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

        $role = Role::updateOrCreate(['id' => $id], $data);

        // Sync permission hanya jika bukan super_user (super_user punya akses penuh via logika di User model)
        if ($role->slug !== 'super_user') {
            $role->permissions()->sync($permissionIds);
        }

        return $this->renderPanel(
            success: $id ? 'Role berhasil diperbarui.' : 'Role baru berhasil ditambahkan.'
        );
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
