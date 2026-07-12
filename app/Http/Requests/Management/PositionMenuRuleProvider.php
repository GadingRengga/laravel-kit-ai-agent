<?php

namespace App\Http\Requests\Management;

/**
 * Rule provider statis untuk sinkronisasi akses menu per posisi
 * (position_has_menus). Sama alasannya dengan MenuRuleProvider — BUKAN
 * FormRequest karena AjaxController men-dispatch method controller secara
 * manual, jadi divalidasi manual lewat Validator::make() di controller.
 */
class PositionMenuRuleProvider
{
    public static function rules(): array
    {
        return [
            'position_id' => ['required', 'integer', 'exists:positions,id'],

            'menu_ids' => ['nullable', 'array'],
            'menu_ids.*' => ['integer', 'exists:menus,id'],

            'access' => ['nullable', 'array'],
            'access.*.can_view' => ['nullable', 'boolean'],
            'access.*.can_create' => ['nullable', 'boolean'],
            'access.*.can_edit' => ['nullable', 'boolean'],
            'access.*.can_delete' => ['nullable', 'boolean'],
        ];
    }

    public static function messages(): array
    {
        return [
            'position_id.required' => 'Posisi tidak valid.',
            'position_id.exists' => 'Posisi tidak ditemukan.',
            'menu_ids.*.exists' => 'Salah satu menu yang dipilih tidak valid.',
        ];
    }
}
