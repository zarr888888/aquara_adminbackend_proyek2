<?php

namespace App\Support;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GombyangResponses
{
    public function answer(string $message, string $key): ?string
    {
        $groq = config('services.gombyang_provider') === 'groq';
        try {
            $response = Http::withToken($key)->acceptJson()->withoutRedirecting()
                ->connectTimeout(4)->timeout(22)->post($groq ? 'https://api.groq.com/openai/v1/responses' : 'https://api.openai.com/v1/responses', [
                    'model' => $groq ? 'openai/gpt-oss-120b' : 'gpt-5.4-mini',
                    'store' => false,
                    'instructions' => 'Anda Gombyang, asisten SAMUDERA. Jawab singkat dalam bahasa Indonesia. Jangan meminta rahasia, mengaku dapat memproses laporan, atau mengarang informasi resmi. Untuk darurat arahkan ke layanan darurat, bukan chat.',
                    'input' => $message,
                    'max_output_tokens' => 650,
                ]);
        } catch (ConnectionException) {
            return null;
        }

        if (! $response->successful() || $response->json('status') !== 'completed' || ! is_array($response->json('output'))) {
            return null;
        }
        $parts = [];
        foreach ($response->json('output') as $item) {
            if (($item['type'] ?? null) !== 'message' || ($item['role'] ?? null) !== 'assistant' || ($item['status'] ?? null) !== 'completed' || ! is_array($item['content'] ?? null)) {
                continue;
            }
            foreach ($item['content'] as $part) {
                if (($part['type'] ?? null) === 'output_text' && is_string($part['text'] ?? null)) {
                    $parts[] = $part['text'];
                }
            }
        }
        $answer = trim(implode("\n", $parts));

        return $answer !== '' ? $answer : null;
    }
}