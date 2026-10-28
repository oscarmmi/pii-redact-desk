<?php

namespace App\Services\Redact;

class PiiMasker
{
    /**
     * Replace supported identifiers before any text is sent to an LLM.
     * Entity metadata intentionally contains counts and placeholder labels only.
     *
     * @return array{redacted_text: string, entities: list<array{type: string, count: int, placeholders: list<string>}>}
     */
    public function mask(string $text): array
    {
        $entities = [];
        $rules = [
            'Order ID' => '/(?<![A-Z0-9])HD-\d+(?!\d)/i',
            'Email' => '/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}\b/i',
            'Phone' => '/(?<![A-Z0-9])(?!\d{4}-\d{2}-\d{2}(?!\d))\+?\d[\d\s().-]{6,}\d(?![A-Z0-9])/i',
        ];

        foreach ($rules as $type => $pattern) {
            $index = 0;
            $placeholders = [];
            $text = preg_replace_callback($pattern, function (array $matches) use ($type, &$index, &$placeholders): string {
                if ($type === 'Phone') {
                    $digits = preg_replace('/\D/', '', $matches[0]);
                    $digitCount = strlen($digits ?? '');

                    if ($digitCount < 8 || $digitCount > 15) {
                        return $matches[0];
                    }
                }

                $index++;
                $label = match ($type) {
                    'Order ID' => 'ORDER_ID',
                    default => strtoupper($type),
                };
                $placeholder = '['.$label.'_'.$index.']';
                $placeholders[] = $placeholder;

                return $placeholder;
            }, $text) ?? $text;

            if ($index > 0) {
                $entities[] = [
                    'type' => $type,
                    'count' => $index,
                    'placeholders' => $placeholders,
                ];
            }
        }

        return [
            'redacted_text' => $text,
            'entities' => $entities,
        ];
    }
}
