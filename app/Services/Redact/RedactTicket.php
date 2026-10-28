<?php

namespace App\Services\Redact;

use App\Models\RedactJob;
use App\Services\Llm\LlmClient;
use Throwable;

class RedactTicket
{
    public function __construct(
        private readonly PiiMasker $masker,
        private readonly LlmClient $llm,
    ) {}

    public function run(string $sourceText): RedactJob
    {
        $job = RedactJob::query()->create([
            'source_text' => $sourceText,
            'status' => 'processing',
        ]);

        $masked = $this->masker->mask($sourceText);
        $summary = null;

        try {
            $summary = $this->llm->summarize($masked['redacted_text']);
        } catch (Throwable) {
            // A model timeout or provider error must not prevent deterministic redaction.
        }

        $job->result()->create([
            'redacted_text' => $masked['redacted_text'],
            'entities' => $masked['entities'],
            'summary' => $summary,
        ]);
        $job->update(['status' => 'completed']);

        return $job->load('result');
    }
}
