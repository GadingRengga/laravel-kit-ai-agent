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

            // BUGFIX (celah keamanan): sebelumnya field ini TIDAK divalidasi
            // sama sekali di server. Frontend punya accept="image/*" tapi itu
            // cuma hint UI, gampang dilewati (request langsung ke endpoint
            // tanpa lewat browser) — jadi siapa pun yang login sebenarnya bisa
            // upload file APAPUN (bukan cuma gambar), ukuran berapa pun,
            // langsung ke storage/app/public/ai-attachments/... yang bisa
            // diakses publik lewat URL. Sekarang dibatasi: maksimal 4 file,
            // wajib gambar asli (bukan cuma cek ekstensi — 'image' rule
            // Laravel cek isi filenya), maksimal 5MB per file.
            'images'   => ['nullable', 'array', 'max:4'],
            'images.*' => ['image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ];
    }
}
