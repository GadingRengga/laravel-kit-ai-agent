<?php

namespace App\Services\AI\Tools;

use App\Models\Superuser\User;
use App\Services\AI\Contracts\AiToolInterface;
use App\Services\AI\DTO\AiToolResult;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;

/**
 * Satu class ini menangani SEMUA tool CRUD yang didefinisikan lewat
 * config/ai_tools.php — jadi nambah operasi CRUD baru buat AI TIDAK perlu
 * bikin class PHP baru, cukup tambah 1 entry config.
 *
 * Mendukung 4 mode operasi (config key 'operation'):
 *   - 'create' (default): Model::create() — butuh draft → user confirm
 *   - 'read':             Model::query()->where(...)->get() — langsung, tanpa draft
 *   - 'update':           Model::find($id)->update(...) — butuh draft → user confirm
 *   - 'delete':           Model::find($id)->delete() — butuh draft → user confirm
 *
 * Kalau butuh business logic lebih rumit (generate nomor invoice, kirim notifikasi,
 * resolve role, dsb), baru bikin Tool class custom terpisah dan daftarkan manual.
 */
class GenericModelTool implements AiToolInterface
{
    public function __construct(private readonly array $config) {}

    public function name(): string
    {
        return $this->config['name'];
    }

    public function description(): string
    {
        return $this->config['description'];
    }

    public function schema(): array
    {
        $properties = [];
        $required = [];

        foreach ($this->config['fields'] ?? [] as $field => $def) {
            $prop = [
                'type'        => $def['type'] ?? 'string',
                'description' => $def['description'] ?? '',
            ];

            // Gemini/OpenAI mewajibkan properti 'items' untuk tipe 'array'
            if ($prop['type'] === 'array') {
                $prop['items'] = $def['items'] ?? ['type' => 'string'];
            }

            $properties[$field] = $prop;

            if (! empty($def['required'])) {
                $required[] = $field;
            }
        }

        return [
            'type'       => 'object',
            'properties' => $properties,
            'required'   => $required,
        ];
    }

    public function toDraft(array $arguments, User $user): AiToolResult
    {
        $this->authorize($user);

        $operation = $this->config['operation'] ?? 'create';

        // READ operation — langsung eksekusi tanpa draft/confirm
        if ($operation === 'read') {
            return $this->executeRead($arguments, $user);
        }

        // CREATE, UPDATE, DELETE — validasi dulu, siapkan draft
        $validated = Validator::make($arguments, $this->validationRules())->validate();

        return AiToolResult::draft(
            modelClass: $this->config['model'],
            payload: $validated,
            summary: $this->renderSummary($validated),
        );
    }

    public function confirm(array $payload, User $user): Model
    {
        $this->authorize($user);

        $operation = $this->config['operation'] ?? 'create';
        $modelClass = $this->config['model'];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException(
                "Model [{$modelClass}] belum ada di project ini. " .
                    "Lengkapi dulu sebelum tool [{$this->name()}] bisa dipakai."
            );
        }

        return match ($operation) {
            'update' => $this->executeUpdate($payload, $user, $modelClass),
            'delete' => $this->executeDelete($payload, $user, $modelClass),
            default  => $this->executeCreate($payload, $user, $modelClass),
        };
    }

    /**
     * Cek apakah tool ini boleh dipakai user — berdasarkan PERMISSION yang
     * dimiliki user (dari role-role yang melekat). Lihat User::hasPermission().
     *
     * Config tool mendukung 2 mode:
     *   - 'permission': slug permission (mis. "user.create") — user harus punya permission ini.
     *   - 'menu' + 'ability' (legacy): tetap didukung untuk backward compat.
     *
     * Tool yang config-nya tidak diisi permission/menu dianggap TERBUKA
     * untuk semua user yang sudah login.
     */
    public function isAllowedFor(User $user): bool
    {
        $permissionSlug = $this->config['permission'] ?? null;

        if ($permissionSlug) {
            return $user->hasPermission($permissionSlug);
        }

        // Fallback ke menu+ability (legacy)
        $menuSlug = $this->config['menu'] ?? null;
        $ability = $this->config['ability'] ?? null;

        if (! $menuSlug || ! $ability) {
            return true;
        }

        return $user->hasMenuAbility($menuSlug, $ability);
    }

    private function authorize(User $user): void
    {
        if ($this->isAllowedFor($user)) {
            return;
        }

        throw new AuthorizationException(
            "Kamu tidak memiliki akses untuk menjalankan tool ini."
        );
    }

    private function validationRules(): array
    {
        $rules = [];

        foreach ($this->config['fields'] ?? [] as $field => $def) {
            $rules[$field] = $def['rules'] ?? (
                ! empty($def['required']) ? ['required'] : ['nullable']
            );
        }

        return $rules;
    }

    /** Ganti placeholder :field_name di summary_template dengan nilai tervalidasi. */
    private function renderSummary(array $validated): string
    {
        $summary = $this->config['summary_template']
            ?? ('Buat data baru: **:' . array_key_first($this->config['fields'] ?? ['data' => null]) . '**');

        foreach ($validated as $key => $value) {
            $summary = str_replace(":{$key}", (string) $value, $summary);
        }

        return $summary;
    }

    /**
     * CREATE — buat record baru di database.
     */
    private function executeCreate(array $payload, User $user, string $modelClass): Model
    {
        $data = $payload;

        if ($stampField = $this->config['stamp_user_as'] ?? null) {
            $data[$stampField] = $user->id;
        }

        return $modelClass::create($data);
    }

    /**
     * READ — cari data berdasarkan filter, langsung return hasil tanpa draft/confirm.
     * Method ini GENERIC untuk entity apapun — field search menyesuaikan
     * dengan kolom yang ada di model.
     *
     * Mendukung loading relasi via config key 'with' + 'allowed_with':
     *   'with' => ['roles', 'employee']         // relasi default selalu di-load
     *   'allowed_with' => ['roles', 'employee']  // whitelist relasi yang diizinkan
     *
     * AI bisa meminta relasi tambahan via field 'with' (array of strings),
     * tapi hanya relasi yang ada di 'allowed_with' yang akan diproses.
     */
    private function executeRead(array $arguments, User $user): AiToolResult
    {
        $modelClass = $this->config['model'];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException(
                "Model [{$modelClass}] belum ada di project ini."
            );
        }

        $query = $modelClass::query();

        // ── Load relasi ───────────────────────────────────────────────────
        // Relasi default dari config + relasi tambahan yang diminta AI
        $defaultWith = $this->config['with'] ?? [];
        $allowedWith = $this->config['allowed_with'] ?? $defaultWith;
        $requestedWith = $arguments['with'] ?? [];

        // Gabung, filter hanya yang diizinkan
        $allWith = array_unique(array_merge($defaultWith, $requestedWith));
        $allWith = array_intersect($allWith, $allowedWith);

        if (! empty($allWith)) {
            $query->with($allWith);
        }

        // ── Filter by ID spesifik ─────────────────────────────────────────
        if (! empty($arguments['id'])) {
            $result = $query->find($arguments['id']);
            return AiToolResult::direct(
                modelClass: $modelClass,
                payload: $arguments,
                summary: 'Menampilkan data detail',
                result: $result ? [$result->toArray()] : [],
            );
        }

        // ── Filter by search keyword ──────────────────────────────────────
        // partial match di kolom umum (name, title, code, dll)
        if (! empty($arguments['search'])) {
            $keyword = $arguments['search'];
            $query->where(function ($q) use ($keyword, $modelClass) {
                $columns = ['name', 'title', 'code', 'email', 'username', 'description'];
                foreach ($columns as $col) {
                    if ($this->columnExists($modelClass, $col)) {
                        $q->orWhere($col, 'like', "%{$keyword}%");
                    }
                }
            });
        }

        // ── Filter by status aktif ────────────────────────────────────────
        if (isset($arguments['is_active'])) {
            if ($this->columnExists($modelClass, 'is_active')) {
                $query->where('is_active', $arguments['is_active']);
            }
        }

        // ── Filter by role (khusus model User) ────────────────────────────
        // Biarkan model handle filtering relasi sendiri
        if (! empty($arguments['role'])) {
            if (method_exists($modelClass, 'roles')) {
                $query->whereHas('roles', function ($q) use ($arguments) {
                    $q->where('name', 'like', "%{$arguments['role']}%");
                });
            }
        }

        // ── Batasi jumlah hasil ──────────────────────────────────────────
        $limit = $arguments['limit'] ?? 10;
        $results = $query->limit($limit)->get();

        return AiToolResult::direct(
            modelClass: $modelClass,
            payload: $arguments,
            summary: 'Menampilkan data',
            result: $results->toArray(),
        );
    }

    /**
     * UPDATE — cari record by ID lalu update field yang dikirim.
     */
    private function executeUpdate(array $payload, User $user, string $modelClass): Model
    {
        $id = $payload['id'] ?? null;

        if (! $id) {
            throw new \RuntimeException("ID harus diisi untuk mengupdate data.");
        }

        $record = $modelClass::find($id);

        if (! $record) {
            throw new \RuntimeException("Data dengan ID {$id} tidak ditemukan.");
        }

        $data = $payload;
        unset($data['id']); // jangan ikut update ID

        $record->update($data);

        return $record->fresh();
    }

    /**
     * DELETE — cari record by ID lalu hapus.
     */
    private function executeDelete(array $payload, User $user, string $modelClass): Model
    {
        $id = $payload['id'] ?? null;

        if (! $id) {
            throw new \RuntimeException("ID harus diisi untuk menghapus data.");
        }

        $record = $modelClass::find($id);

        if (! $record) {
            throw new \RuntimeException("Data dengan ID {$id} tidak ditemukan.");
        }

        // Simpan data sebelum dihapus supaya bisa direturn
        $record->delete();

        return $record;
    }

    /**
     * Cek apakah suatu kolom ada di tabel model tertentu.
     */
    private function columnExists(string $modelClass, string $column): bool
    {
        try {
            $instance = new $modelClass;
            $table = $instance->getTable();
            $columns = \Schema::getColumnListing($table);
            return in_array($column, $columns, true);
        } catch (\Throwable) {
            return false;
        }
    }
}
