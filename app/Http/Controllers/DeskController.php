<?php

namespace App\Http\Controllers;

use App\Models\RedactJob;
use App\Services\Redact\RedactTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DeskController extends Controller
{
    public function index(): Response
    {
        $sampleTickets = RedactJob::query()
            ->where('status', 'sample')
            ->latest('id')
            ->limit(4)
            ->get(['id', 'source_text'])
            ->map(fn (RedactJob $ticket): array => [
                'id' => $ticket->id,
                'source_text' => $ticket->source_text,
                'title' => $this->ticketTitle($ticket->source_text),
            ])
            ->values();

        return Inertia::render('Desk', [
            'sampleTickets' => $sampleTickets,
            'summaryMode' => $this->summaryMode(),
        ]);
    }

    public function redact(Request $request, RedactTicket $redactTicket): JsonResponse
    {
        $validated = $request->validate([
            'source_text' => ['required', 'string', 'min:8', 'max:12000'],
        ]);

        $job = $redactTicket->run($validated['source_text']);
        $result = $job->result;

        return response()->json([
            'id' => $job->id,
            'source_text' => $job->source_text,
            'redacted_text' => $result?->redacted_text,
            'entities' => $result->entities,
            'summary' => $result?->summary,
            'status' => $job->status,
            'summary_mode' => $this->summaryMode(),
        ]);
    }

    private function ticketTitle(string $sourceText): string
    {
        $firstSentence = preg_split('/(?<=[.!?])\s+/', $sourceText, 2)[0] ?? 'Support ticket';

        return mb_substr($firstSentence, 0, 48).(mb_strlen($firstSentence) > 48 ? '…' : '');
    }

    private function summaryMode(): string
    {
        if (app()->environment('testing') || blank(config('services.gemini.key'))) {
            return 'Local demo';
        }

        return 'Gemini 2.5 Flash';
    }
}
