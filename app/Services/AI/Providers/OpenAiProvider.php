<?php

namespace App\Services\AI\Providers;

use App\Models\Ai\AiConnection;
use App\Services\AI\Contracts\AiProviderInterface;
use App\Services\AI\DTO\AiChatResponse;
use App\Services\AI\DTO\ToolCallDTO;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiProvider implements AiProviderInterface
{
    public function chat(AiConnection $connection, array $messages, array $toolSchemas): AiChatResponse
    {
        $config = config('ai.providers.openai');
        $model = $connection->resolvedModel();

        // BUGFIX (balasan AI kosong): model reasoning (o1/o3/gpt-5.x, dst)
        // memakai budget token yang SAMA untuk "mikir" (reasoning token,
        // tidak terlihat oleh user) dan untuk jawaban yang benar-benar
        // dikirim balik. Kalau ai.max_response_tokens (default 500) terlalu
        // kecil, bisa saja seluruh budget habis untuk reasoning sebelum
        // sempat nulis jawaban → content kosong. Analog dengan fix yang
        // sudah ada di GeminiProvider (maxOutputTokens di-clamp minimal
        // 2048 untuk model 3.x). Selain itu, endpoint chat/completions
        // MENOLAK parameter `max_tokens` untuk model reasoning — wajib
        // pakai `max_completion_tokens`.
        $isReasoningModel = (bool) preg_match('/^(o[0-9]|gpt-5)/i', $model);

        $payload = [
            'model'    => $model,
            'messages' => $messages,
        ];

        if ($isReasoningModel) {
            $payload['max_completion_tokens'] = max((int) config('ai.max_response_tokens'), 2048);
        } else {
            $payload['max_tokens'] = config('ai.max_response_tokens');
        }

        // Cuma sertakan 'tools' kalau memang ada — kirim array kosong tetap
        // menambah sedikit token overhead di beberapa model.
        if (! empty($toolSchemas)) {
            $payload['tools'] = $toolSchemas;
            $payload['tool_choice'] = 'auto';
        }

        $response = Http::withToken($connection->api_key)
            ->timeout($config['timeout'])
            ->baseUrl($config['base_url'])
            ->post('/chat/completions', $payload);

        if ($response->failed()) {
            // Jangan bocorkan body mentah (bisa berisi detail internal) ke user,
            // cukup log lalu lempar exception generik.
            report(new RuntimeException('OpenAI API error: ' . $response->body()));
            throw new RuntimeException('Gagal menghubungi ChatGPT. Coba lagi sebentar lagi.');
        }

        $json = $response->json();
        $choice = $json['choices'][0]['message'] ?? [];

        $toolCalls = [];
        foreach ($choice['tool_calls'] ?? [] as $call) {
            $toolCalls[] = new ToolCallDTO(
                id: $call['id'],
                name: $call['function']['name'],
                arguments: json_decode($call['function']['arguments'], true) ?? [],
            );
        }

        return new AiChatResponse(
            content: $choice['content'] ?? null,
            toolCalls: $toolCalls,
            promptTokens: $json['usage']['prompt_tokens'] ?? 0,
            completionTokens: $json['usage']['completion_tokens'] ?? 0,
        );
    }
}
