<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\Role;
use App\Models\Superuser\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class UserRoleController extends Controller
{
    /**
     * Halaman daftar user + role assignment.
     */
    public function index(): View
    {
        return view('pages.superuser.user-role.index', [
            'users' => $this->users(),
        ]);
    }

    /**
     * Form edit roles untuk user tertentu.
     */
    public function edit(Request $request): View
    {
        $id = $request->input('data');
        $user = User::with('roles')->findOrFail($id);

        return view('pages.superuser.user-role.partials._form', [
            'user' => $user,
            'roles' => Role::orderBy('name')->get(),
            'selectedRoleIds' => $user->roles->pluck('id')->all(),
        ]);
    }

    /**
     * Simpan roles untuk user.
     */
    public function update(Request $request): View
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'role_ids' => 'nullable|array',
            'role_ids.*' => 'exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();

        if (!isset($data['user_id']) || !is_numeric($data['user_id'])) {
            return $this->renderPanel(error: 'ID user tidak valid.');
        }

        $user = User::find((int) $data['user_id']);

        if (!$user) {
            return $this->renderPanel(error: 'User tidak ditemukan.');
        }

        DB::beginTransaction();

        try {
            $user->roles()->sync($data['role_ids'] ?? []);

            DB::commit();

            return $this->renderPanel(
                success: 'Role untuk user "' . $user->name . '" berhasil disimpan.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(
                error: 'Terjadi kesalahan saat menyimpan role user. Coba lagi nanti.'
            );
        }
    }

    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.user-role.partials._panel', [
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
}
