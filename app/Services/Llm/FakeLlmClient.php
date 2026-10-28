<?php

namespace App\Services\Llm;

class FakeLlmClient implements LlmClient
{
    public ?string $lastInput = null;

    public function summarize(string $redactedText): string
    {
        $this->lastInput = $redactedText;
        $excerpt = mb_substr(trim(preg_replace('/\s+/', ' ', $redactedText) ?? $redactedText), 0, 220);

        return 'Demo summary: '.rtrim($excerpt, ' .').'.';
    }
}
