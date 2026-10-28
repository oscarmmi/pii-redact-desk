<?php

use App\Models\RedactJob;
use App\Services\Llm\FakeLlmClient;
use App\Services\Llm\GeminiLlmClient;
use App\Services\Llm\LlmClient;
use Illuminate\Support\Facades\Http;

it('redacts a guest ticket and calls the fake LLM with redacted text only', function (): void {
    $fake = new FakeLlmClient;
    app()->instance(LlmClient::class, $fake);

    $sourceText = 'Please email taylor@example.com about order HD-48291.';
    $response = $this->postJson('/redact', ['source_text' => $sourceText]);

    $response->assertOk()
        ->assertJsonPath('status', 'completed')
        ->assertJsonPath('source_text', $sourceText)
        ->assertJsonPath('redacted_text', 'Please email [EMAIL_1] about order [ORDER_ID_1].')
        ->assertJsonPath('entities.0.type', 'Order ID')
        ->assertJsonPath('entities.1.type', 'Email')
        ->assertJsonPath('summary', 'Demo summary: Please email [EMAIL_1] about order [ORDER_ID_1].');

    expect($fake->lastInput)->toBe('Please email [EMAIL_1] about order [ORDER_ID_1].')
        ->not->toContain('taylor@example.com')
        ->not->toContain('HD-48291');

    expect(RedactJob::query()->firstOrFail()->result->entities)
        ->not->toContain('taylor@example.com');
});

it('binds the fake LLM in tests when no provider key is configured', function (): void {
    config(['services.gemini.key' => null]);

    expect(app(LlmClient::class))->toBeInstanceOf(FakeLlmClient::class);
});

it('keeps deterministic redaction available when the LLM times out', function (): void {
    $llm = Mockery::mock(LlmClient::class);
    $llm->shouldReceive('summarize')->once()->with('Email [EMAIL_1] for [ORDER_ID_1].')->andThrow(new RuntimeException('Timed out'));
    app()->instance(LlmClient::class, $llm);

    $this->postJson('/redact', [
        'source_text' => 'Email taylor@example.com for HD-48291.',
    ])->assertOk()
        ->assertJsonPath('redacted_text', 'Email [EMAIL_1] for [ORDER_ID_1].')
        ->assertJsonPath('summary', null)
        ->assertJsonPath('status', 'completed');
});

it('uses the configured Gemini client with a bounded request timeout', function (): void {
    config([
        'services.gemini.key' => 'test-key',
        'services.gemini.model' => 'gemini-2.5-flash',
        'services.gemini.timeout' => 12,
    ]);
    Http::fake([
        'generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => 'A redacted ticket summary.']]]]],
        ]),
    ]);

    $client = new GeminiLlmClient('test-key');

    expect($client->summarize('Order [ORDER_ID_1] needs a replacement.'))->toBe('A redacted ticket summary.');

    Http::assertSent(fn ($request): bool => $request->hasHeader('x-goog-api-key', 'test-key')
        && str_contains($request['contents'][0]['parts'][0]['text'], 'Order [ORDER_ID_1] needs a replacement.')
        && ! str_contains($request['contents'][0]['parts'][0]['text'], 'HD-')
    );
});
