<?php

namespace App\Services\AI\Tools;

use App\Models\Superuser\User;
use App\Services\AI\Contracts\AiToolInterface;
use App\Services\AI\DTO\AiToolResult;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Illuminate\Support\Facades\Validator;

/**
 * Satu class ini menangani SEMUA tool CRUD + ANALISIS yang didefinisikan
 * lewat config/ai_tools.php — jadi nambah operasi baru buat AI TIDAK perlu
 * bikin class PHP baru, cukup tambah 1 entry config.
 *
 * Mendukung 5 mode operasi (config key 'operation'):
 *   - 'create'    (default): Model::create() — butuh draft → user confirm
 *   - 'read':                Model::query()->where(...)->get() — langsung, tanpa draft
 *   - 'update':              Model::find($id)->update(...) — butuh draft → user confirm
 *   - 'delete':              Model::find($id)->delete() — butuh draft → user confirm
 *   - 'aggregate':           SUM/COUNT/AVG (opsional GROUP BY) — langsung, tanpa draft
 *
 * Kalau butuh business logic lebih rumit (generate nomor invoice, kirim notifikasi,
 * resolve role, join kompleks lintas tabel, dsb), baru bikin Tool class custom
 * terpisah dan daftarkan manual di AiServiceProvider — bukan lewat file ini.
 *
 * ─── SCOPE / ISOLASI DATA ──────────────────────────────────────────────────
 * Config tool bisa mendeklarasikan 'scope' supaya AI TIDAK PERNAH bisa
 * membaca/menganalisis data di luar cakupan user yang sedang chat:
 *   'scope' => 'own_department'  → where('department_id', $user->department_id)
 *   'scope' => 'own_records'     → where('created_by', $user->id)
 *   (tidak diisi / null)         → tidak di-scope (HANYA untuk tool yang memang
 *                                   sengaja global, mis. User untuk admin)
 * Scope ini WAJIB diisi untuk entity bisnis yang harus terisolasi per
 * user/departemen (quotation, order, project, dst) — kalau lupa, ini jadi
 * celah IDOR: user bisa baca/analisis data milik user/departemen lain.
 */
class GenericModelTool implements AiToolInterface
{
    /** Cache hasil columnExists per model+column dalam 1 request — hindari query berulang */
    private array $columnCache = [];

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

        // ── Inject field filter generic berdasarkan config ─────────────────
        // Supaya tidak perlu menulis date_from/date_to/xxx_min/xxx_max manual
        // di tiap entry config/ai_tools.php — cukup declare 'date_column'
        // dan/atau 'numeric_filters', field-nya otomatis muncul di schema
        // yang dikirim ke AI (dipakai bareng oleh operation 'read' & 'aggregate').
        if (($this->config['operation'] ?? 'create') === 'read' || ($this->config['operation'] ?? null) === 'aggregate') {
            if ($this->config['date_column'] ?? null) {
                $properties['date_from'] = ['type' => 'string', 'description' => 'Filter tanggal mulai, format YYYY-MM-DD'];
                $properties['date_to']   = ['type' => 'string', 'description' => 'Filter tanggal akhir, format YYYY-MM-DD'];
            }

            foreach ($this->config['numeric_filters'] ?? [] as $col) {
                $properties["{$col}_min"] = ['type' => 'number', 'description' => "Nilai minimum untuk kolom {$col}"];
                $properties["{$col}_max"] = ['type' => 'number', 'description' => "Nilai maksimum untuk kolom {$col}"];
            }
        }

        // ── Field khusus operation 'aggregate' ─────────────────────────────
        if (($this->config['operation'] ?? null) === 'aggregate') {
            $properties['metric'] = [
                'type'        => 'string',
                'description' => 'Jenis perhitungan: "count" (jumlah data), "sum" (total), atau "avg" (rata-rata). Default: count.',
            ];
            $properties['column'] = [
                'type'        => 'string',
                'description' => 'Kolom yang dihitung. WAJIB diisi kalau metric bukan "count". Pilihan: '
                    . implode(', ', $this->config['aggregatable'] ?? []),
            ];
            $properties['group_by'] = [
                'type'        => 'string',
                'description' => 'Kelompokkan hasil per kolom ini (opsional). Pilihan: '
                    . implode(', ', $this->config['groupable'] ?? []),
            ];
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

        // READ / AGGREGATE — langsung eksekusi tanpa draft/confirm (read-only)
        if ($operation === 'read') {
            return $this->executeRead($arguments, $user);
        }

        if ($operation === 'aggregate') {
            return $this->executeAggregate($arguments, $user);
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
     * Batasi query ke cakupan yang boleh dilihat/dianalisis user ini.
     * Dipanggil di awal executeRead() DAN executeAggregate() — jangan sampai
     * salah satu operasi baru lupa memanggil ini, karena itu jadi celah IDOR.
     *
     * Kalau config tidak mendeklarasikan 'scope' sama sekali, TIDAK di-scope
     * (dianggap sengaja global, mis. tool User untuk admin). Untuk entity
     * bisnis (quotation/order/project/dll), WAJIB isi 'scope' di config.
     */
    private function applyScope(Builder $query, User $user): void
    {
        $scope = $this->config['scope'] ?? null;

        match ($scope) {
            'own_department' => $query->where('department_id', $user->department_id ?? -1),
            'own_records'    => $query->where('created_by', $user->id),
            default          => null,
        };
    }

    /**
     * Filter yang dipakai bersama oleh operation 'read' DAN 'aggregate':
     * search keyword, status aktif, filter relasi, rentang tanggal, dan
     * rentang numerik. Diekstrak jadi 1 method supaya kedua operasi selalu
     * konsisten (nambah filter baru cukup di satu tempat).
     */
    private function applyCommonFilters(Builder $query, array $arguments, string $modelClass): void
    {
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

        // ── Filter by relasi (generic) ────────────────────────────────────
        // Config tool bisa mendefinisikan 'relation_filters' => [
        //     'role'  => ['relation' => 'roles', 'field' => 'name'],
        //     'group' => ['relation' => 'groups', 'field' => 'name'],
        // ]
        // AI bisa mengirim 'role' sebagai argumen, dan filter ini akan
        // otomatis menjalankan whereHas pada relasi yang sesuai.
        $relationFilters = $this->config['relation_filters'] ?? [];
        foreach ($relationFilters as $argKey => $filterDef) {
            if (! empty($arguments[$argKey])) {
                $relation = $filterDef['relation'] ?? $argKey;
                $field    = $filterDef['field'] ?? 'name';
                if (method_exists($modelClass, $relation)) {
                    $query->whereHas($relation, function ($q) use ($arguments, $argKey, $field) {
                        $q->where($field, 'like', "%{$arguments[$argKey]}%");
                    });
                }
            }
        }

        // ── Filter rentang tanggal ─────────────────────────────────────────
        // Config: 'date_column' => 'created_at'. AI kirim date_from/date_to
        // (format YYYY-MM-DD). Dipakai utk pertanyaan "bulan ini", "kuartal
        // lalu", dst — AI yang menerjemahkan bahasa natural jadi tanggal
        // eksplisit sebelum memanggil tool ini.
        if ($dateColumn = $this->config['date_column'] ?? null) {
            if ($this->columnExists($modelClass, $dateColumn)) {
                if (! empty($arguments['date_from'])) {
                    $query->whereDate($dateColumn, '>=', $arguments['date_from']);
                }
                if (! empty($arguments['date_to'])) {
                    $query->whereDate($dateColumn, '<=', $arguments['date_to']);
                }
            }
        }

        // ── Filter rentang numerik (>=, <=) ────────────────────────────────
        // Config: 'numeric_filters' => ['total_value']. AI kirim
        // total_value_min / total_value_max. Whitelist eksplisit dari config
        // supaya AI tidak bisa filter kolom sensitif sembarangan.
        foreach ($this->config['numeric_filters'] ?? [] as $col) {
            if (! $this->columnExists($modelClass, $col)) {
                continue;
            }
            if (isset($arguments["{$col}_min"]) && is_numeric($arguments["{$col}_min"])) {
                $query->where($col, '>=', $arguments["{$col}_min"]);
            }
            if (isset($arguments["{$col}_max"]) && is_numeric($arguments["{$col}_max"])) {
                $query->where($col, '<=', $arguments["{$col}_max"]);
            }
        }
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
     *
     * CATATAN PENTING: method ini untuk DAFTAR/DETAIL data (dibatasi limit),
     * BUKAN untuk pertanyaan "berapa total/rata-rata" — untuk itu AI harus
     * memakai tool operation 'aggregate' (lihat executeAggregate()), supaya
     * angkanya dihitung PASTI oleh database, bukan ditaksir dari daftar yang
     * mungkin terpotong limit/pagination.
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

        $this->applyScope($query, $user);

        // ── Load relasi ───────────────────────────────────────────────────
        $defaultWith = $this->config['with'] ?? [];
        $allowedWith = $this->config['allowed_with'] ?? $defaultWith;
        $requestedWith = $arguments['with'] ?? [];

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

        $this->applyCommonFilters($query, $arguments, $modelClass);

        // ── Pagination & batasi jumlah hasil ─────────────────────────────
        // Dukung offset/pagination: AI bisa kirim 'limit' (default 10)
        // dan 'page' (default 1) untuk mengambil halaman berikutnya.
        $limit = (int) ($arguments['limit'] ?? 10);
        $page  = (int) ($arguments['page'] ?? 1);
        $results = $query->limit($limit)->offset(($page - 1) * $limit)->get();

        return AiToolResult::direct(
            modelClass: $modelClass,
            payload: $arguments,
            summary: 'Menampilkan data',
            result: $results->toArray(),
        );
    }

    /**
     * AGGREGATE — hitung SUM/COUNT/AVG (opsional GROUP BY) di level database.
     * INI YANG MEMBUAT "menganalisa" jadi akurat: angka dihitung PHP/SQL,
     * bukan LLM menjumlahkan sendiri dari daftar baris di dalam teks jawaban
     * (yang gampang salah & tidak konsisten kalau ditanya ulang).
     *
     * Config wajib untuk tool jenis ini:
     *   'aggregatable' => ['total_value']   // whitelist kolom yang boleh di-sum/avg
     *   'groupable'    => ['status']        // whitelist kolom yang boleh di-group by
     * Dua whitelist ini WAJIB ada — jangan pernah percaya nama kolom mentah
     * dari AI tanpa dicek dulu, supaya tidak bisa dipakai mengagregasi/
     * mengelompokkan kolom sensitif yang tidak dimaksudkan (mis. password,
     * catatan internal).
     */
    private function executeAggregate(array $arguments, User $user): AiToolResult
    {
        $modelClass = $this->config['model'];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException(
                "Model [{$modelClass}] belum ada di project ini."
            );
        }

        $query = $modelClass::query();

        $this->applyScope($query, $user);
        $this->applyCommonFilters($query, $arguments, $modelClass);

        $metric  = $arguments['metric'] ?? 'count';
        $column  = $arguments['column'] ?? null;
        $groupBy = $arguments['group_by'] ?? null;

        if (! in_array($metric, ['count', 'sum', 'avg'], true)) {
            throw new \InvalidArgumentException(
                "Metric [{$metric}] tidak dikenal. Pakai: count, sum, atau avg."
            );
        }

        // ── Whitelist kolom yang boleh diagregasi ──────────────────────────
        $allowedMetrics = $this->config['aggregatable'] ?? [];
        if ($metric !== 'count') {
            if (! $column) {
                throw new \InvalidArgumentException(
                    "Kolom (parameter 'column') wajib diisi untuk metric [{$metric}]."
                );
            }
            if (! in_array($column, $allowedMetrics, true)) {
                throw new \InvalidArgumentException(
                    "Kolom [{$column}] tidak bisa diagregasi untuk tool ini. Pilihan: "
                        . implode(', ', $allowedMetrics)
                );
            }
        }

        // ── Whitelist kolom group by ────────────────────────────────────────
        $allowedGroupBy = $this->config['groupable'] ?? [];
        if ($groupBy && ! in_array($groupBy, $allowedGroupBy, true)) {
            throw new \InvalidArgumentException(
                "Tidak bisa mengelompokkan berdasarkan [{$groupBy}]. Pilihan: "
                    . implode(', ', $allowedGroupBy)
            );
        }

        if ($groupBy) {
            $selectRaw = match ($metric) {
                'sum' => "SUM({$column}) as value",
                'avg' => "AVG({$column}) as value",
                default => "COUNT(*) as value",
            };

            // limit(50): cap cardinality — cegah group_by kolom ber-kardinalitas
            // tinggi (mis. accidental group by "email") membuat response raksasa.
            $rows = $query->select($groupBy)
                ->selectRaw($selectRaw)
                ->groupBy($groupBy)
                ->orderByDesc('value')
                ->limit(50)
                ->get()
                ->toArray();

            return AiToolResult::direct(
                modelClass: $modelClass,
                payload: $arguments,
                summary: 'Menampilkan hasil analisis per kelompok',
                result: $rows,
            );
        }

        $value = match ($metric) {
            'sum' => (float) $query->sum($column),
            'avg' => (float) $query->avg($column),
            default => $query->count(),
        };

        return AiToolResult::direct(
            modelClass: $modelClass,
            payload: $arguments,
            summary: 'Menampilkan hasil analisis',
            result: ['value' => $value, 'metric' => $metric, 'column' => $column],
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
     * Hasil di-cache per (modelClass + column) dalam 1 request
     * supaya tidak query berulang ke information_schema.
     */
    private function columnExists(string $modelClass, string $column): bool
    {
        $cacheKey = "{$modelClass}:{$column}";

        if (array_key_exists($cacheKey, $this->columnCache)) {
            return $this->columnCache[$cacheKey];
        }

        try {
            $instance = new $modelClass;
            $table = $instance->getTable();
            $columns = SchemaFacade::getColumnListing($table);
            $this->columnCache[$cacheKey] = in_array($column, $columns, true);
        } catch (\Throwable) {
            $this->columnCache[$cacheKey] = false;
        }

        return $this->columnCache[$cacheKey];
    }
}
