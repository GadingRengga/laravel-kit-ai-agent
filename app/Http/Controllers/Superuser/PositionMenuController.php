<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\PositionMenuRuleProvider;
use App\Models\Position;
use App\Models\Superuser\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class PositionMenuController extends Controller
{
    /**
     * Halaman penuh Akses Menu per Posisi.
     * GET /superuser/position-menu
     */
    public function index(): View
    {



        return view('pages.superuser.position-menu.index', [
            'positions' => $this->positions(),
        ]);
    }

    /**
     * Konten matrix akses (menu x can_view/can_create/can_edit/can_delete)
     * untuk 1 posisi, dimuat ke modal lewat LiveDomJS.
     * Dipanggil dari: live-click="editAccess({id})" live-target="#modal-lg"
     */
    public function editAccess($request): View
    {
        $id = $request['data'];
        $position = Position::with(['department', 'menus'])->findOrFail($id);

        $data = view('pages.superuser.position-menu.partials._access-form', [
            'position' => $position,
            'menus' => $this->menuTree(),
        ]);

        return $data;
    }


    /**
     * Simpan matrix akses (sync tabel pivot position_has_menus).
     * Dipanggil dari: live-click="saveAccess" live-target="#position-menu-panel"
     *
     * position_id dikirim lewat hidden input di dalam live-scope yang sama
     * (bukan argumen inline) supaya seluruh checkbox matrix ikut ter-
     * serialisasi otomatis bersama form-nya — pola ini sama dengan
     * store/update di MenuController yang mengandalkan scope, bukan argumen.
     */
    public function saveAccess(Request $request): View
    {
        $validator = Validator::make(
            $request->all(),
            PositionMenuRuleProvider::rules(),
            PositionMenuRuleProvider::messages()
        );

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $position = Position::findOrFail($data['position_id']);

        $access = $data['access'] ?? [];
        $menuIds = $data['menu_ids'] ?? [];

        $sync = [];
        foreach ($menuIds as $menuId) {
            $row = $access[$menuId] ?? [];
            $sync[$menuId] = [
                'can_view'   => (bool) ($row['can_view'] ?? false),
                'can_create' => (bool) ($row['can_create'] ?? false),
                'can_edit'   => (bool) ($row['can_edit'] ?? false),
                'can_delete' => (bool) ($row['can_delete'] ?? false),
            ];
        }

        // sync() aman dipakai declarative di sini karena menuTree() di atas
        // selalu mengirim SELURUH menu yang ada saat ini sebagai baris
        // matrix — jadi $sync merepresentasikan keadaan akses yang utuh,
        // bukan sebagian. Baris pivot utk menu yang tidak lagi dicentang
        // sama sekali otomatis ikut ter-detach.
        $position->menus()->sync($sync);

        return $this->renderPanel(
            success: 'Akses menu untuk posisi "' . $position->name . '" berhasil disimpan.'
        );
    }

    /**
     * Render ulang seluruh panel (#position-menu-panel): alert + tabel posisi.
     */
    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.position-menu.partials._panel', [
            'positions' => $this->positions(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    /**
     * Daftar posisi + departemen + jumlah menu yang boleh "dilihat"
     * (dipakai sebagai indikator ringkas di tabel).
     */
    protected function positions()
    {
        return Position::with('department')
            ->withCount([
                'menus as menus_count' => function ($q) {
                    $q->where('position_has_menus.can_view', 1);
                }
            ])
            ->orderBy('name')
            ->get();
    }
    /**
     * Menu level-1 (root) beserta children — sama seperti
     * MenuController::tree(), dipakai sebagai daftar baris pada matrix akses.
     */
    protected function menuTree()
    {
        return Menu::whereNull('parent_id')
            ->with(['children' => fn($q) => $q->orderBy('order')])
            ->orderBy('order')
            ->get();
    }
}
