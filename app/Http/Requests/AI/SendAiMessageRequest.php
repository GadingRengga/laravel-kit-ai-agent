<?php

namespace App\Http\Requests\AI;

use Illuminate\Foundation\Http\FormRequest;

class SendAiMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // otorisasi per-resource sudah dihandle di masing-masing Tool
    }

    public function rules(): array
    {
        return [
            'message' => ['required', 'string', 'max:4000'],
            // context opsional: nama halaman saat ini, dipakai buat batasi
            // tools yang dikirim ke AI (lihat AiChatController::allowedToolsForContext)
            'context' => ['nullable', 'string', 'in:customer,quotation,order'],
        ];
    }
}
