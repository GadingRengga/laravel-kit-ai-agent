<?php

namespace App\Services\AI\Tools;

use App\Models\Superuser\User;
use App\Services\AI\Contracts\AiToolInterface;
use App\Services\AI\DTO\AiToolResult;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

/**
 * Satu class ini menangani SEMUA tool yang didefinisikan lewat
 * config/ai_tools.php — jadi nambah "function" baru buat AI TIDAK perlu
 * bikin Action + Tool class baru tiap kali, cukup tambah 1 entry config.
 *
 * Dipakai untuk kasus umum: "AI kumpulin beberapa field dari user lalu
 * Model::create(...) langsung". Kalau butuh business logic lebih rumit
 * (generate nomor invoice, kirim notifikasi, dsb), baru bikin Tool class
 * custom terpisah (lihat pola lama di CreateCustomerTool.php) dan daftarkan
 * manual — jangan dipaksakan lewat sini.
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
            $properties[$field] = [
                'type'        => $def['type'] ?? 'string',
                'description' => $def['description'] ?? '',
            ];

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

        $modelClass = $this->config['model'];

        if (! class_exists($modelClass)) {
            throw new \RuntimeException(
                "Model [{$modelClass}] belum ada di project ini. " .
                    "Lengkapi dulu sebelum tool [{$this->name()}] bisa dipakai."
            );
        }

        $data = $payload;

        if ($stampField = $this->config['stamp_user_as'] ?? null) {
            $data[$stampField] = $user->id;
        }

        return $modelClass::create($data);
    }

    /** Skip Gate check kalau 'ability' tidak diisi di config — biar gampang dipakai tanpa Policy dulu. */
    private function authorize(User $user): void
    {
        $ability = $this->config['ability'] ?? null;
        if (! $ability) {
            return;
        }

        Gate::forUser($user)->authorize($ability, $this->config['model']);
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
}
