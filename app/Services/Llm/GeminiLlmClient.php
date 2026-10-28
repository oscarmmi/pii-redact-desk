<?php

namespace App\Services\Llm;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiLlmClient implements LlmClient
{
    public function __construct(
        private readonly string $apiKey,
        private readonly string $model = 'gemini-2.5-flash',
        private readonly int $timeout = 12,
    ) {}

    public function summarize(string $redactedText): string
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders(['x-goog-api-key' => $this->apiKey])
            ->post('https://generativelanguage.googleapis.com/v1beta/models/'.$this->model.':generateContent', [
                'contents' => [[
                    'parts' => [[
                        'text' => "Summarize this support ticket in 1–2 concise sentences. It has already been redacted. Do not infer or reconstruct masked information.\n\n".$redactedText,
                    ]],
                ]],
                'generationConfig' => [
                    'temperature' => 0.2,
                    'maxOutputTokens' => 120,
                ],
            ]);

        $response->throw();

        $summary = $response->json('candidates.0.content.parts.0.text');

        if (! is_string($summary) || trim($summary) === '') {
            throw new RuntimeException('Gemini returned an empty summary.');
        }

        return trim($summary);
    }
}
