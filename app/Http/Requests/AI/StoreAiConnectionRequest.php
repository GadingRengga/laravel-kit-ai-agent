<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class StoreAiConnectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_code' => ['required', 'string', 'exists:ai_providers,code'],
            'api_key'       => ['required', 'string', 'min:10'],
            'default_model' => ['nullable', 'string', 'max:100'],
        ];
    }
}
