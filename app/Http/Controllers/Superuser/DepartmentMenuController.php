<?php

namespace App\Http\Controllers\Superuser;

use App\Http\Controllers\Controller;
use App\Http\Requests\Management\DepartmentMenuRuleProvider;
use App\Models\Department;
use App\Models\Superuser\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * Fitur "Akses Menu per Department" — mis. department Marketing cuma
 * boleh lihat menu Marketing & Dashboard. Ini LANGKAH AWAL sebelum AI
 * dibatasi per hak akses menu: begitu department punya daftar menu yang
 * jelas, tool AI (lihat config/ai_tools.php) tinggal dicocokkan terhadap
 * daftar ini sebelum tool dieksekusi.
 *
 * Polanya sengaja disamakan persis dengan MenuController: live-scope
 * "Superuser.DepartmentMenuController", panel di-render ulang penuh
 * setelah update supaya modal ikut "tertutup" dan tabel selalu fresh.
 */
class DepartmentMenuController extends Controller
{
    /**
     * Halaman penuh Akses Menu per Department.
     * GET /superuser/department-menu
     */
    public function index(): View
    {
        return view('pages.superuser.department-menu.index', [
            'departments' => $this->departments(),
        ]);
    }

    /**
     * Form checklist menu untuk 1 department, dimuat ke modal.
     * Dipanggil dari: live-click="edit({id})" live-target="#modal-md"
     */
    public function edit($request): View
    {
        $id = $request['data'];
        $department = Department::findOrFail($id);

        return view('pages.superuser.department-menu.partials._form', [
            'department' => $department,
            'menus' => $this->menuTree(),
            'selectedIds' => $department->menus()->pluck('menus.id')->all(),
        ]);
    }

    /**
     * Simpan (sync) menu yang boleh diakses department.
     * Dipanggil dari: live-click="update" live-target="#department-menu-panel"
     * (id department dikirim lewat hidden input di scope, sama seperti
     * pola MenuController::update — bukan lewat argumen live-click).
     */
    public function update(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            DepartmentMenuRuleProvider::rules(),
            DepartmentMenuRuleProvider::messages()
        );

        if ($validator->fails()) {
            return $this->renderPanel(error: $validator->errors()->first());
        }

        $data = $validator->validated();
        $department = Department::findOrFail($data['id']);

        // sync() otomatis handle tambah/hapus baris pivot sesuai centang
        // terbaru — menu yang di-uncheck otomatis lepas aksesnya.
        $department->menus()->sync($data['menu_ids'] ?? []);

        return $this->renderPanel(
            success: 'Akses menu untuk department "' . $department->name . '" berhasil disimpan.'
        );
    }

    /**
     * Render ulang seluruh panel (#department-menu-panel): alert + tabel
     * department. Dipakai sebagai response update supaya tabel & badge
     * jumlah menu selalu fresh.
     */
    protected function renderPanel(?string $error = null, ?string $success = null): View
    {
        return view('pages.superuser.department-menu.partials._panel', [
            'departments' => $this->departments(),
            'error' => $error,
            'success' => $success,
        ]);
    }

    /** Semua department, beserta jumlah menu yang sudah di-assign. */
    protected function departments()
    {
        return Department::withCount('menus')
            ->orderBy('name')
            ->get();
    }

    /**
     * Menu aktif level-1 (root) beserta children-nya, dipakai untuk render
     * checklist bertingkat di form. Sengaja hanya menu aktif — menu
     * nonaktif tidak relevan untuk diberi akses.
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
