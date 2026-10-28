<?php

namespace App\Services\Llm;

interface LlmClient
{
    public function summarize(string $redactedText): string;
}
