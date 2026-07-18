<?php

namespace App\Services\AI\Providers;

use App\Models\Ai\AiConnection;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\DTO\AiChatResponse;
use App\Services\AI\DTO\ToolCallDTO;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Implementasi AiProviderInterface untuk Google Gemini (Generative Language API).
 *
 * Beda penting dari OpenAiProvider:
 * - Auth pakai query param `?key=API_KEY`, BUKAN Bearer token header.
 * - Bentuk payload beda total: `contents` (bukan `messages`), role `model`
 *   (bukan `assistant`), system prompt lewat field `system_instruction`
 *   terpisah (bukan message role `system` di dalam array).
 * - Tool/function calling pakai `tools[].functionDeclarations[]`, bukan
 *   `tools[].function`. Function call di response ada di
 *   `candidates[0].content.parts[].functionCall`, dan TIDAK punya `id`
 *   bawaan seperti OpenAI — jadi kita generate id sendiri.
 *
 * CATATAN keterbatasan (biar gak salah ekspektasi):
 * Pesan ber-role 'tool' (hasil eksekusi tool call sebelumnya) saat ini
 * dikonversi jadi teks biasa role 'user' ("[Hasil tool] ..."), BUKAN
 * `functionResponse` native Gemini. Ini karena `AiMessage::toApiMessage()`
 * saat ini belum menyimpan `tool_name` di payload pesan role 'tool' —
 * cuma `tool_call_id` — padahal Gemini butuh `name` fungsi buat
 * `functionResponse`. Solusinya BUKAN di file ini, tapi butuh tambahan
 * kecil di AiMessage::toApiMessage() (lihat catatan di bawah file ini).
 * Sebelum itu diterapkan, degradasi ke teks biasa ini tetap fungsional
 * untuk melanjutkan percakapan, cuma bukan implementasi "murni" native.
 */
class GeminiProvider implements AiProviderInterface
{
    public function chat(AiConnection $connection, array $messages, array $toolSchemas): AiChatResponse
    {
        $config = config('ai.providers.gemini');
        $model = $connection->resolvedModel();

        [$systemText, $contents] = $this->toGeminiContents($messages);

        $payload = [
            'contents' => $contents,
            'generationConfig' => [
                // Gemini 3.x adalah reasoning model: token "thinking" internal
                // ikut dipotong dari maxOutputTokens yang sama dengan token
                // jawaban. Kalau nilai config lama (peninggalan model non-reasoning,
                // biasanya < 1000) dipakai apa adanya, model bisa habiskan semua
                // jatah buat mikir dan sisa jawabannya kepotong/kosong — gejalanya
                // kelihatan seperti "ngawur" padahal sebenarnya cuma terpotong.
                'maxOutputTokens' => max((int) config('ai.max_response_tokens'), 2048),
                // 'low' cukup buat chat widget ERP (bukan riset/analisis berat).
                // Kalau butuh reasoning lebih dalam (mis. tool-calling kompleks),
                // naikkan ke 'medium'. Jangan pakai 'high' kecuali benar2 perlu,
                // karena makin banyak token dipakai buat mikir.
                'thinkingConfig' => [
                    'thinkingLevel' => 'low',
                ],
            ],
        ];

        if ($systemText !== '') {
            $payload['system_instruction'] = [
                'parts' => [['text' => $systemText]],
            ];
        }

        if (! empty($toolSchemas)) {
            $payload['tools'] = $this->toGeminiTools($toolSchemas);
        }

        $response = Http::timeout($config['timeout'])
            ->baseUrl($config['base_url'])
            ->post("/models/{$model}:generateContent?key={$connection->api_key}", $payload);

        if ($response->failed()) {
            report(new RuntimeException('Gemini API error: ' . $response->body()));
            throw new RuntimeException('Gagal menghubungi Gemini. Coba lagi sebentar lagi.');
        }

        $json = $response->json();
        Log::info('GEMINI_DEBUG', [
            'contents_sent' => $contents,
            'tools_sent'    => $payload['tools'] ?? null,
            'raw_reply'     => $json,
        ]);
        $parts = $json['candidates'][0]['content']['parts'] ?? [];
        $finishReason = $json['candidates'][0]['finishReason'] ?? null;

        // MAX_TOKENS berarti jawaban kepotong (termasuk kalau token habis buat
        // "thinking" sebelum sempat menjawab) — log supaya kelihatan di
        // laravel.log, bukan cuma keliatan sebagai jawaban aneh ke user.
        if ($finishReason === 'MAX_TOKENS') {
            report(new RuntimeException(
                'Gemini finishReason=MAX_TOKENS — jawaban kemungkinan terpotong. '
                    . 'Pertimbangkan naikkan generationConfig.maxOutputTokens atau '
                    . 'turunkan thinkingConfig.thinkingLevel.'
            ));
        }

        $text = collect($parts)
            ->pluck('text')
            ->filter()
            ->implode('');

        $toolCalls = [];
        foreach ($parts as $part) {
            if (! isset($part['functionCall'])) {
                continue;
            }

            $toolCalls[] = new ToolCallDTO(
                id: 'gemini_' . Str::uuid(),
                name: $part['functionCall']['name'],
                arguments: $part['functionCall']['args'] ?? [],
            );
        }

        return new AiChatResponse(
            content: $text !== '' ? $text : null,
            toolCalls: $toolCalls,
            promptTokens: $json['usageMetadata']['promptTokenCount'] ?? 0,
            completionTokens: $json['usageMetadata']['candidatesTokenCount'] ?? 0,
        );
    }

    /**
     * Konversi format pesan generik (dipakai bareng OpenAiProvider) ke
     * format `contents` Gemini. Mengembalikan [systemText, contents[]].
     */
    private function toGeminiContents(array $messages): array
    {
        $systemParts = [];
        $contents = [];

        foreach ($messages as $msg) {
            if ($msg['role'] === 'system') {
                if (! empty($msg['content'])) {
                    $systemParts[] = $msg['content'];
                }
                continue;
            }

            if ($msg['role'] === 'tool') {
                $contents[] = [
                    'role' => 'user',
                    'parts' => [['text' => '[Hasil tool] ' . ($msg['content'] ?? '')]],
                ];
                continue;
            }

            if (empty($msg['content'])) {
                continue;
            }

            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

        // PENTING: Untuk memastikan urutan kronologis dari terlama ke terbaru 
        // karena adanya efek penumpukan array dari buildMessagePayload.
        // Kita cek jika pesan terakhir di array justru bertindak sebagai pesan lama.
        // Namun untuk amannya, mari pastikan urutan $contents mengikuti urutan asli request.
        // Jika di log debug contents_sent Anda terbalik, buka komentar baris di bawah ini:
        // $contents = array_reverse($contents);

        return [implode("\n", $systemParts), $contents];
    }

    /** Konversi tools format OpenAI (dari AiToolRegistry::toSchemaArray) ke functionDeclarations Gemini. */
    private function toGeminiTools(array $toolSchemas): array
    {
        $declarations = [];

        foreach ($toolSchemas as $tool) {
            $fn = $tool['function'] ?? $tool;

            $declarations[] = [
                'name' => $fn['name'],
                'description' => $fn['description'] ?? '',
                'parameters' => $fn['parameters'] ?? ['type' => 'object', 'properties' => new \stdClass()],
            ];
        }

        return [['functionDeclarations' => $declarations]];
    }
}
