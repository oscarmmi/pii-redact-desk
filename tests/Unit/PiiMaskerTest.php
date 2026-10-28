<?php

use App\Services\Redact\PiiMasker;

it('masks email, phone, and Harbor Desk order identifiers with safe metadata', function (): void {
    $result = (new PiiMasker)->mask('Email taylor@example.com, call +1 (202) 555-0147 about order HD-48291.');

    expect($result['redacted_text'])
        ->toContain('[EMAIL_1]')
        ->toContain('[PHONE_1]')
        ->toContain('[ORDER_ID_1]')
        ->not->toContain('taylor@example.com')
        ->not->toContain('202) 555-0147')
        ->not->toContain('HD-48291');

    expect($result['entities'])->toBe([
        ['type' => 'Order ID', 'count' => 1, 'placeholders' => ['[ORDER_ID_1]']],
        ['type' => 'Email', 'count' => 1, 'placeholders' => ['[EMAIL_1]']],
        ['type' => 'Phone', 'count' => 1, 'placeholders' => ['[PHONE_1]']],
    ]);
});

it('leaves unsupported digit strings and ordinary text untouched', function (): void {
    $result = (new PiiMasker)->mask('Ticket opened on 2025-01-17. No contact details were supplied.');

    expect($result['redacted_text'])->toBe('Ticket opened on 2025-01-17. No contact details were supplied.')
        ->and($result['entities'])->toBe([]);
});
