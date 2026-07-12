<?php

namespace App\Http\Requests\Management;

use Illuminate\Validation\Rule;

/**
 * Rule provider statis untuk Menu.
 *
 * Sengaja BUKAN FormRequest — AjaxController men-dispatch method controller
 * secara manual (bypass DI container Laravel), jadi FormRequest tidak bisa
 * ter-resolve/ter-validasi otomatis lewat type-hint seperti pada route biasa.
 * Controller memanggil ini langsung lewat Validator::make().
 */
class MenuRuleProvider
{
    /**
     * @param  int|null  $ignoreId  id menu yang sedang diedit (null saat create)
     */
    public static function rules(?int $ignoreId = null): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'slug' => [
                'nullable',
                'string',
                'max:100',
                'alpha_dash',
                Rule::unique('menus', 'slug')->ignore($ignoreId),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                'exists:menus,id',
                Rule::notIn(array_filter([$ignoreId])),
            ],
            'icon' => ['nullable', 'string', 'max:100'],
            'route' => ['nullable', 'string', 'max:150'],
            'order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'name.required' => 'Nama menu wajib diisi.',
            'slug.alpha_dash' => 'Slug hanya boleh huruf, angka, strip, dan underscore.',
            'slug.unique' => 'Slug sudah dipakai menu lain.',
            'parent_id.exists' => 'Parent menu tidak valid.',
            'parent_id.not_in' => 'Menu tidak boleh menjadi parent dirinya sendiri.',
        ];
    }
}
