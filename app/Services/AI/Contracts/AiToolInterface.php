<?php

namespace App\Services\AI\Contracts;

use App\Models\Superuser\User;
use App\Services\AI\DTO\AiToolResult;

interface AiToolInterface
{
    /** Nama unik, dikirim ke API sebagai function name. Snake_case. */
    public function name(): string;

    /**
     * Penjelasan singkat kapan tool ini dipakai — INI yang dibaca AI untuk
     * memutuskan, jadi tulis jelas & singkat. Ini menggantikan kebutuhan
     * menulis aturan panjang di system prompt.
     */
    public function description(): string;

    /** JSON Schema untuk parameter, format OpenAI function calling. */
    public function schema(): array;

    /**
     * Validasi argumen dari AI + siapkan sebagai draft (BELUM nulis ke DB).
     * Lempar ValidationException kalau argumen tidak valid — akan ditangani
     * AiChatService dan dikirim balik ke AI sebagai tool response supaya
     * AI bisa memperbaiki / tanya ulang ke user.
     */
    public function toDraft(array $arguments, User $user): AiToolResult;

    /**
     * Eksekusi nyata setelah user menekan "Konfirmasi". Payload di sini
     * adalah payload yang SUDAH tervalidasi dan sudah (mungkin) diedit user,
     * bukan argumen mentah dari AI lagi.
     */
    public function confirm(array $payload, User $user): \Illuminate\Database\Eloquent\Model;
}
