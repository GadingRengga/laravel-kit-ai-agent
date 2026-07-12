<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\MenuRuleProvider;
use App\Models\Superuser\Menu;
use Illuminate\Http\Request;
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
        return view('management.menu.index', [
            'menus' => $this->tree(),
        ]);
    }

    /**
     * Konten form kosong (create), dimuat ke dalam modal lewat LiveDomJS.
     * Dipanggil dari: live-click="create" live-target="#menu-form-modal"
     */
    public function create(): View
    {
        return view('management.menu.partials._form', [
            'menu' => new Menu(['is_active' => true, 'order' => 0]),
            'parents' => $this->parentOptions(),
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

        return view('management.menu.partials._form', [
            'menu' => $menu,
            'parents' => $this->parentOptions($menu->id),
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
    public function destroy(int $id): View
    {
        $menu = Menu::withCount('children')->findOrFail($id);

        if ($menu->children_count > 0) {
            return $this->renderPanel(
                error: 'Menu "' . $menu->name . '" masih punya submenu. Hapus atau pindahkan submenu-nya dulu.'
            );
        }

        $menu->delete();

        return $this->renderPanel(success: 'Menu "' . $menu->name . '" berhasil dihapus.');
    }

    /**
     * Logika bersama create/edit-submit. AjaxController memanggil method
     * controller secara manual (bukan lewat route model binding Laravel),
     * jadi validasi dilakukan manual di sini, bukan lewat FormRequest.
     */
    protected function save(Request $request): View
    {
        $id = $request->id;
        $validator = Validator::make(
            $request->all(),
            MenuRuleProvider::rules($id),
            MenuRuleProvider::messages()
        );

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $data['slug'] = $data['slug'] ?: Str::slug($data['name']);
        $data['order'] = $data['order'] ?? 0;
        $data['is_active'] = $request->boolean('is_active');

        Menu::updateOrCreate(['id' => $id], $data);

        return $this->renderPanel(
            success: $id ? 'Menu berhasil diperbarui.' : 'Menu baru berhasil ditambahkan.'
        );
    }

    /**
     * Render ulang seluruh panel (#menu-panel): toolbar + alert + tabel.
     * Dipakai sebagai response store/update/destroy supaya tabel selalu
     * fresh dan modal otomatis "tertutup" (karena subtree modal-shell
     * ikut di-render ulang dalam keadaan bersih/kosong).
     */
    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('management.menu.partials._panel', [
            'menus' => $this->tree(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    /**
     * Ambil menu level-1 (root) beserta children-nya, terurut.
     * Starter kit ini membatasi 2 level (menu utama + submenu) supaya
     * tampilan sidebar tetap sederhana — Menu model sendiri sebenarnya
     * mendukung parent_id berjenjang tanpa batas kalau nanti mau dikembangkan.
     */
    protected function tree()
    {
        return Menu::whereNull('parent_id')
            ->with(['children' => fn($q) => $q->orderBy('order')])
            ->orderBy('order')
            ->get();
    }

    /**
     * Opsi dropdown "Parent Menu" — hanya menu root, minus dirinya sendiri
     * kalau sedang edit (mencegah menu jadi parent dari dirinya sendiri).
     */
    protected function parentOptions(?int $excludeId = null)
    {
        return Menu::whereNull('parent_id')
            ->when($excludeId, fn($q) => $q->where('id', '!=', $excludeId))
            ->orderBy('order')
            ->pluck('name', 'id');
    }
}
