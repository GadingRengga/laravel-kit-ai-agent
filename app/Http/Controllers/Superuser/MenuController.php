<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Models\Superuser\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;


class MenuController extends Controller
{
    /**
     * Halaman penuh Manajemen Menu.
     * GET /management/menu — route biasa (lihat routes-snippet/web.php).
     */
    public function index(): View
    {
        return view('pages.superuser.menu.index', [
            'menus' => $this->tree(),
        ]);
    }

    /**
     * Konten form kosong (create), dimuat ke dalam modal lewat LiveDomJS.
     * Dipanggil dari: live-click="create" live-target="#menu-form-modal"
     */
    public function create(): View
    {
        return view('pages.superuser.menu.partials._form', [
            'menu' => new Menu(['is_active' => true, 'order' => 0]),
            'parents' => $this->parentOptions(),
            'parentOptions' => $this->buildParentOptions(collect()),
        ]);
    }

    /**
     * Konten form terisi data existing (edit), dimuat ke modal.
     * Dipanggil dari: live-click="edit({id})" live-target="#menu-form-modal"
     */
    public function edit($request): View
    {
        $id = $request['data'];
        $menu = Menu::findOrFail($id);

        return view('pages.superuser.menu.partials._form', [
            'menu' => $menu,
            'parents' => $this->parentOptions($menu->id),
            'parentOptions' => $this->buildParentOptions(collect(), $menu->id),
        ]);
    }

    /**
     * Simpan menu baru.
     * Dipanggil dari: live-click="store" live-target="#menu-panel"
     */
    public function store(Request $request): View
    {
        return $this->save($request, null);
    }

    /**
     * Update menu existing.
     * Dipanggil dari: live-click="update({id})" live-target="#menu-panel"
     */
    public function update(Request $request)
    {

        return $this->save($request);
    }

    /**
     * Hapus menu. Menu yang masih punya submenu tidak boleh dihapus
     * (harus hapus/pindahkan submenu-nya dulu).
     * Dipanggil dari: live-click="destroy({id})" live-target="#menu-panel"
     */
    public function destroy(Request $request): View
    {
        $id = $request->input('data');

        if (!is_numeric($id)) {
            return $this->renderPanel(error: 'ID menu tidak valid.');
        }

        $menu = Menu::withCount('children')->find((int) $id);

        if (!$menu) {
            return $this->renderPanel(error: 'Menu tidak ditemukan atau sudah dihapus.');
        }

        if ($menu->children_count > 0) {
            return $this->renderPanel(
                error: 'Menu "' . $menu->name . '" masih punya submenu. Hapus atau pindahkan submenu-nya dulu.'
            );
        }

        DB::beginTransaction();

        try {
            $menuName = $menu->name;

            $menu->delete();

            DB::commit();

            return $this->renderPanel(success: "Menu \"{$menuName}\" berhasil dihapus.");
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(error: 'Terjadi kesalahan saat menghapus menu. Coba lagi nanti.');
        }
    }

    /**
     * Logika bersama create/edit-submit. AjaxController memanggil method
     * controller secara manual (bukan lewat route model binding Laravel),
     * jadi validasi dilakukan manual di sini, bukan lewat FormRequest.
     */
    protected function save(Request $request): View
    {
        $id = $request->id;
        $rules = [
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:menus,slug,' . ($id ?: 'NULL') . ',id',
            'description' => 'nullable|string|max:500',
            'icon' => 'nullable|string|max:255',
            'route' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:menus,id',
            'order' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['order'] = $data['order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        DB::beginTransaction();

        try {
            Menu::updateOrCreate(['id' => $id], $data);

            DB::commit();

            return $this->renderPanel(
                success: $id ? 'Menu berhasil diperbarui.' : 'Menu baru berhasil ditambahkan.'
            );
        } catch (\Throwable $e) {
            DB::rollBack();

            return $this->renderPanel(
                error: 'Terjadi kesalahan saat menyimpan menu. Coba lagi nanti.'
            );
        }
    }

    /**
     * Render ulang seluruh panel (#menu-panel): toolbar + alert + tabel.
     * Dipakai sebagai response store/update/destroy supaya tabel selalu
     * fresh dan modal otomatis "tertutup" (karena subtree modal-shell
     * ikut di-render ulang dalam keadaan bersih/kosong).
     */
    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.menu.partials._panel', [
            'menus' => $this->tree(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    /**
     * Ambil menu root beserta seluruh descendants (unlimited depth), terurut.
     */
    protected function tree()
    {
        return Menu::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('order')
            ->get();
    }

    /**
     * Opsi dropdown "Parent Menu" — semua menu kecuali dirinya sendiri
     * kalau sedang edit (mencegah menu jadi parent dari dirinya sendiri).
     */
    protected function parentOptions(?int $excludeId = null)
    {
        return Menu::when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('order')
            ->pluck('name', 'id');
    }

    /**
     * Build parent options dengan indentasi untuk dropdown form.
     *
     * @param  \Illuminate\Support\Collection  $flatIds  [id => name] dari parentOptions()
     * @param  int|null  $selectedId
     * @return array
     */
    protected function buildParentOptions($flatIds, $selectedId = null): array
    {
        $tree = Menu::whereNull('parent_id')
            ->with('allChildren')
            ->orderBy('order')
            ->get();

        $result = [];
        $this->flattenMenuTree($tree, $result, '');

        // Filter out excluded id (current menu being edited)
        if ($selectedId) {
            $result = array_filter($result, fn($item) => $item['id'] != $selectedId);
        }

        return $result;
    }

    /**
     * Flatten menu tree recursively dengan prefix indentasi.
     */
    protected function flattenMenuTree($menus, &$result, $prefix): void
    {
        foreach ($menus as $menu) {
            $result[] = [
                'id' => $menu->id,
                'name' => $menu->name,
                'prefix' => $prefix,
            ];
            if ($menu->children->isNotEmpty()) {
                $this->flattenMenuTree($menu->children, $result, $prefix . '&nbsp;&nbsp;&nbsp;&nbsp;');
            }
        }
    }
}
