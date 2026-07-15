<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Halaman daftar users.
     */
    public function index(): View
    {
        return view('pages.superuser.user.index', [
            'users' => $this->users(),
        ]);
    }

    /**
     * Form create user di modal.
     */
    public function create(): View
    {
        return view('pages.superuser.user.partials._form', [
            'user' => new User(['is_active' => true]),
            'roles' => $this->allRoles(),
            'selectedRoleIds' => [],
        ]);
    }

    /**
     * Form edit user di modal.
     */
    public function edit(Request $request): View
    {
        $id = $request->input('data');
        $user = User::with('roles')->findOrFail($id);

        return view('pages.superuser.user.partials._form', [
            'user' => $user,
            'roles' => $this->allRoles(),
            'selectedRoleIds' => $user->roles->pluck('id')->all(),
        ]);
    }

    /**
     * Form edit role user (alias dari edit).
     */
    public function editRole(Request $request): View
    {
        return $this->edit($request);
    }

    /**
     * Simpan user baru.
     */
    public function store(Request $request): View
    {
        return $this->save($request, null);
    }

    /**
     * Update user.
     */
    public function update(Request $request): View
    {
        return $this->save($request, $request->id);
    }

    /**
     * Hapus user.
     */
    public function destroy(int $id): View
    {
        $user = User::findOrFail($id);

        // Prevent deleting superuser
        if ($user->isSuperUser()) {
            return $this->renderPanel(error: 'User Super User tidak bisa dihapus.');
        }

        // Detach all roles before deleting
        $user->roles()->detach();
        $user->delete();

        return $this->renderPanel(success: 'User "' . $user->name . '" berhasil dihapus.');
    }

    /**
     * Toggle status aktif/nonaktif user.
     */
    public function toggleStatus(Request $request): View
    {
        $id = $request->input('data');
        $user = User::findOrFail($id);

        if ($user->isSuperUser()) {
            return $this->renderPanel(error: 'Status Super User tidak bisa diubah.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return $this->renderPanel(success: "User \"{$user->name}\" berhasil {$status}.");
    }

    protected function save(Request $request, ?int $id = null): View
    {
        $rules = [
            'name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username,' . ($id ?: 'NULL') . ',id',
            'email' => 'required|string|email|max:255|unique:users,email,' . ($id ?: 'NULL') . ',id',
            'password' => $id ? 'nullable|string|min:8|confirmed' : 'required|string|min:8|confirmed',
            'avatar' => 'nullable|string|max:255',
            'is_active' => 'boolean',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['is_active'] = $request->boolean('is_active');

        $roleIds = $data['role_ids'] ?? [];
        unset($data['role_ids']);

        // Hash password jika diisi
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Hapus password_confirmation sebelum simpan
        unset($data['password_confirmation']);

        $user = User::updateOrCreate(['id' => $id], $data);

        // Sync roles
        $user->roles()->sync($roleIds);

        $message = $id ? 'User berhasil diperbarui.' : 'User baru berhasil ditambahkan.';
        return $this->renderPanel(success: $message);
    }

    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.user.partials._panel', [
            'users' => $this->users(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    protected function users()
    {
        return User::with('roles')
            ->withCount('roles as roles_count')
            ->orderBy('name')
            ->get();
    }

    protected function allRoles()
    {
        return \App\Models\Superuser\Role::orderBy('name')->get();
    }
}
