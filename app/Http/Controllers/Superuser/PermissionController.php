<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\Menu;
use App\Models\Superuser\Permission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PermissionController extends Controller
{
    /**
     * Halaman daftar permission.
     */
    public function index(): View
    {
        return view('pages.superuser.permission.index', [
            'permissions' => $this->allPermissions(),
        ]);
    }

    /**
     * Form create permission di modal.
     */
    public function create(): View
    {
        return view('pages.superuser.permission.partials._form', [
            'permission' => new Permission(),
            'menus' => $this->menuTree(),
            'selectedMenuIds' => [],
        ]);
    }

    /**
     * Form edit permission di modal.
     */
    public function edit(Request $request): View
    {
        $id = $request->input('data');
        $permission = Permission::with('menus')->findOrFail($id);

        return view('pages.superuser.permission.partials._form', [
            'permission' => $permission,
            'menus' => $this->menuTree(),
            'selectedMenuIds' => $permission->menus->pluck('id')->all(),
        ]);
    }

    /**
     * Simpan permission baru.
     */
    public function store(Request $request): View
    {
        return $this->save($request, null);
    }

    /**
     * Update permission.
     */
    public function update(Request $request): View
    {
        return $this->save($request, $request->id);
    }

    /**
     * Hapus permission.
     */
    public function destroy(Request $request): View
    {
        $id = $request->input('data');

        if (!is_numeric($id)) {
            return $this->renderPanel(error: 'ID permission tidak valid.');
        }

        $permission = Permission::find((int) $id);

        if (!$permission) {
            return $this->renderPanel(error: 'Permission tidak ditemukan atau sudah dihapus.');
        }

        DB::beginTransaction();

        try {
            $permName = $permission->name;

            $permission->roles()->detach();
            $permission->menus()->detach();
            $permission->delete();

            DB::commit();

            return $this->renderPanel(success: "Permission \"{$permName}\" berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(error: 'Terjadi kesalahan saat menghapus permission. Coba lagi nanti.');
        }
    }

    protected function save(Request $request, ?int $id = null): View
    {
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:permissions,slug,' . ($id ?: 'NULL') . ',id',
            'group' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'menu_ids' => 'nullable|array',
            'menu_ids.*' => 'exists:menus,id',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);

        $menuIds = $data['menu_ids'] ?? [];
        unset($data['menu_ids']);

        DB::beginTransaction();

        try {
            $permission = Permission::updateOrCreate(['id' => $id], $data);
            $permission->menus()->sync($menuIds);

            DB::commit();

            return $this->renderPanel(
                success: $id ? 'Permission berhasil diperbarui.' : 'Permission baru berhasil ditambahkan.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(
                error: 'Terjadi kesalahan saat menyimpan permission. Coba lagi nanti.'
            );
        }
    }

    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.permission.partials._panel', [
            'permissions' => $this->allPermissions(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    protected function allPermissions()
    {
        return Permission::withCount('roles', 'menus')
            ->orderBy('group')
            ->orderBy('name')
            ->get();
    }

    /**
     * Menu tree untuk checklist di form.
     */
    protected function menuTree()
    {
        return Menu::whereNull('parent_id')
            ->where('is_active', true)
            ->with(['children' => fn($q) => $q->where('is_active', true)->orderBy('order')])
            ->orderBy('order')
            ->get();
    }
}
