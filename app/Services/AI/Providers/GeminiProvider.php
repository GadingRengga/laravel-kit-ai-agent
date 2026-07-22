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
 * CATATAN PENTING — Function Calling di Gemini:
 * Gemini menolak history yang berisi functionCall native tanpa ditutup
 * functionResponse native (dengan content berupa OBJECT). Karena tool
 * response kita selalu string teks biasa, functionCall + functionResponse
 * native TIDAK BISA dipakai.
 *
 * Solusi: SEMUA pesan yang terkait functionCall/toolCall dikirim sebagai
 * teks biasa:
 *   - Assistant dengan tool_calls → "[AI memanggil tool: nama_tool]"
 *   - Tool response (role='tool')  → di-skip (sudah diwakili teks di atas)
 *
 * Ini membuat Gemini tidak pernah melihat functionCall native, sehingga
 * tidak perlu functionResponse. Alternasi role tetap valid (model → user).
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

            // ── Tool response → skip ──────────────────────────────────────
            // Tool response dikirim sebagai teks di Gemini karena:
            // 1. functionResponse native butuh content berupa OBJECT — response
            //    kita selalu string teks biasa.
            // 2. functionCall yang tidak ditutup functionResponse native akan
            //    dianggap error oleh Gemini.
            // Solusi aman: skip tool response, functionCall-nya juga dikirim
            // sebagai teks biasa agar alternasi role tetap valid.
            if ($msg['role'] === 'tool') {
                // Skip — tidak perlu dikirim karena functionCall-nya juga
                // akan dikirim sebagai teks biasa (bukan format native).
                continue;
            }

            // ── Assistant dengan tool_calls → kirim sebagai teks biasa ────
            // JANGAN kirim sebagai functionCall native Gemini karena:
            // 1. functionCall WAJIB ditutup functionResponse (format object)
            // 2. Kita tidak bisa kirim functionResponse native karena content
            //    tool response kita string, bukan object.
            // 3. Kalau functionCall menggantung → Gemini error.
            // Solusi: kirim sebagai teks biasa, beri prefix "[AI memanggil tool]".
            if ($msg['role'] === 'assistant' && ! empty($msg['tool_calls'])) {
                $toolNames = collect($msg['tool_calls'])->pluck('function.name')->implode(', ');

                $text = '[AI memanggil tool: ' . $toolNames . ']';

                if (! empty($msg['content'])) {
                    $text = $msg['content'] . "\n\n" . $text;
                }

                $contents[] = [
                    'role' => 'model',
                    'parts' => [['text' => $text]],
                ];
                continue;
            }

            // ── Skip pesan kosong tanpa tool_calls ────────────────────────
            if (empty($msg['content'])) {
                continue;
            }

            // ── User / Assistant biasa (teks saja) ────────────────────────
            $contents[] = [
                'role' => $msg['role'] === 'assistant' ? 'model' : 'user',
                'parts' => [['text' => $msg['content']]],
            ];
        }

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
