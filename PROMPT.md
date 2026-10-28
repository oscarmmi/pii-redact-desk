Build PII Redact Desk in THIS folder: C:\xampp\htdocs\pii-redact-desk

A recruiter-ready demo that redacts emails, phones, and order IDs from support tickets before an LLM sees them.

Laravel 13 + Vue 3 starter kit is already here if present. Reuse it. Do not create a second app inside NewPortfolio.

## Must satisfy

- Models: `RedactJob` (source_text, status), `RedactResult` (redact_job_id, redacted_text, entities json, summary nullable).
- Deterministic `App\Services\Redact\PiiMasker` first (email, E.164-ish phone, Harbor Desk order HD-\d+). Then optional LLM summary of the redacted text. Timeout 12s. Never 500. The LLM must receive only redacted text.
- `App\Services\Llm\LlmClient` with `FakeLlmClient` in tests and when no `GEMINI_API_KEY`. Optional Gemini when key is set.
- Seed 4 fictional Harbor Desk tickets with mixed PII. English only. No real people.
- UI: paste or load sample, show original vs redacted, entity chips, optional summary.
- Pest: email and order id are masked in redacted_text; LLM client is called with redacted text only; FakeLlm bound; guest POST 200.
- README mermaid: Ticket → PII mask → LLM summary of redacted text. How to run without API key.

## GitHub

- git config user.name "Oscar Munoz" / user.email "oscarxxi@gmail.com"
- Commit: feat: add pii redact desk
- Public https://github.com/oscarmmi/pii-redact-desk
- gh repo edit --description "Redact emails, phones, and order IDs before an LLM summarizes a ticket (Laravel + Vue)." --add-topic laravel --add-topic vue --add-topic ai --add-topic php --add-topic security
- Stop. Print the URL.

No Impeccable. No Redis. No real PII.
