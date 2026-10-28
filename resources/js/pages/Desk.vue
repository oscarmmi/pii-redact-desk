<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';

type EntityGroup = {
    type: string;
    count: number;
    placeholders: string[];
};

type SampleTicket = {
    id: number;
    title: string;
    source_text: string;
};

type RedactionResult = {
    id: number;
    source_text: string;
    redacted_text: string;
    entities: EntityGroup[];
    summary: string | null;
    status: string;
};

const props = defineProps<{
    sampleTickets: SampleTicket[];
    summaryMode: string;
}>();

const activeSample = ref<number | null>(null);
const sourceText = ref('');
const result = ref<RedactionResult | null>(null);
const isSubmitting = ref(false);
const errorMessage = ref('');

const entityCount = computed(
    () =>
        result.value?.entities.reduce(
            (total, entity) => total + entity.count,
            0,
        ) ?? 0,
);

function loadSample(ticket: SampleTicket) {
    activeSample.value = ticket.id;
    sourceText.value = ticket.source_text;
    result.value = null;
    errorMessage.value = '';
}

function updateText() {
    activeSample.value = null;
    result.value = null;
    errorMessage.value = '';
}

async function redactTicket() {
    if (isSubmitting.value) return;

    isSubmitting.value = true;
    errorMessage.value = '';

    try {
        const csrfToken = document.querySelector<HTMLMetaElement>(
            'meta[name="csrf-token"]',
        )?.content;
        const response = await fetch('/redact', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
            },
            body: JSON.stringify({ source_text: sourceText.value }),
        });

        const payload = await response.json();

        if (!response.ok) {
            errorMessage.value =
                payload.message ??
                'This ticket could not be processed. Please try again.';
            return;
        }

        result.value = payload as RedactionResult;
    } catch {
        errorMessage.value =
            'The desk could not be reached. Your text is still here—please try again.';
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Head title="PII Redact Desk" />
    <div class="min-h-screen bg-[#f7f8f6] text-[#202720]">
        <header class="border-b border-[#e4e8e2] bg-white">
            <div
                class="mx-auto flex max-w-[1440px] items-center justify-between px-5 py-4 sm:px-8"
            >
                <a
                    href="/"
                    class="flex items-center gap-3"
                    aria-label="PII Redact Desk home"
                >
                    <span
                        class="flex size-10 items-center justify-center rounded-xl bg-[#153b2a] text-white shadow-sm"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                d="M12 3 5 6v5c0 4.6 2.9 8.3 7 10 4.1-1.7 7-5.4 7-10V6l-7-3Z"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linejoin="round"
                            />
                            <path
                                d="m9 12 2 2 4-4"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold tracking-tight"
                            >Harbor Desk</span
                        >
                        <span class="block text-xs text-[#748077]"
                            >Privacy workspace</span
                        >
                    </span>
                </a>
                <div
                    class="flex items-center gap-2 rounded-full border border-[#dce8df] bg-[#f5faf6] px-3 py-1.5 text-xs font-medium text-[#326747]"
                >
                    <span class="size-1.5 rounded-full bg-[#4d9664]"></span>
                    PII guard active
                </div>
            </div>
        </header>

        <main class="mx-auto max-w-[1440px] px-5 pt-9 pb-14 sm:px-8 sm:pt-12">
            <div
                class="mb-8 flex flex-col justify-between gap-6 lg:flex-row lg:items-end"
            >
                <div class="max-w-2xl">
                    <p
                        class="mb-3 text-xs font-semibold tracking-[0.18em] text-[#56735f] uppercase"
                    >
                        Support operations · secure by design
                    </p>
                    <h1
                        class="text-3xl font-semibold tracking-[-0.04em] text-[#1d2b22] sm:text-[40px]"
                    >
                        Redact first. Summarize second.
                    </h1>
                    <p
                        class="mt-3 max-w-xl text-[15px] leading-7 text-[#69756c]"
                    >
                        Strip customer identifiers from a support ticket before
                        it reaches an AI model. Review every replacement before
                        sharing a summary.
                    </p>
                </div>
                <div
                    class="flex items-center gap-3 rounded-xl border border-[#e5e9e3] bg-white px-4 py-3 shadow-[0_2px_10px_rgba(23,45,30,0.03)]"
                >
                    <span
                        class="flex size-9 items-center justify-center rounded-lg bg-[#f0f5f0] text-[#3f6849]"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            class="size-5"
                            aria-hidden="true"
                        >
                            <path
                                d="M4 6.5h16M4 12h16M4 17.5h10"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-xs font-medium text-[#768178]"
                            >Summary engine</span
                        >
                        <span
                            class="mt-0.5 block text-sm font-semibold text-[#324a38]"
                            >{{ props.summaryMode }}</span
                        >
                    </span>
                </div>
            </div>

            <div
                class="mb-5 grid gap-3 rounded-xl border border-[#e3e9e1] bg-white p-3 sm:grid-cols-3 sm:p-4"
            >
                <div class="flex items-center gap-3 rounded-lg px-2 py-2">
                    <span
                        class="flex size-8 items-center justify-center rounded-full bg-[#edf4ee] text-xs font-semibold text-[#3e704d]"
                        >01</span
                    >
                    <div>
                        <p class="text-sm font-semibold">Paste a ticket</p>
                        <p class="mt-0.5 text-xs text-[#849087]">
                            Original stays in this workspace
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 rounded-lg px-2 py-2 sm:border-x sm:border-[#edf0eb] sm:px-5"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-full bg-[#edf4ee] text-xs font-semibold text-[#3e704d]"
                        >02</span
                    >
                    <div>
                        <p class="text-sm font-semibold">Mask identifiers</p>
                        <p class="mt-0.5 text-xs text-[#849087]">
                            Deterministic, before any model call
                        </p>
                    </div>
                </div>
                <div
                    class="flex items-center gap-3 rounded-lg px-2 py-2 sm:pl-5"
                >
                    <span
                        class="flex size-8 items-center justify-center rounded-full bg-[#edf4ee] text-xs font-semibold text-[#3e704d]"
                        >03</span
                    >
                    <div>
                        <p class="text-sm font-semibold">Get a safe summary</p>
                        <p class="mt-0.5 text-xs text-[#849087]">
                            Only redacted text can leave the app
                        </p>
                    </div>
                </div>
            </div>

            <div
                class="grid items-start gap-5 xl:grid-cols-[minmax(0,1.03fr)_minmax(0,0.97fr)]"
            >
                <section
                    class="overflow-hidden rounded-2xl border border-[#e1e7df] bg-white shadow-[0_8px_30px_rgba(28,48,34,0.04)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#edf0eb] px-5 py-4 sm:px-6"
                    >
                        <div>
                            <p class="text-sm font-semibold">Ticket input</p>
                            <p class="mt-1 text-xs text-[#849087]">
                                Paste text or start from a fictional sample
                            </p>
                        </div>
                        <span
                            class="rounded-md bg-[#f4f6f3] px-2 py-1 text-[10px] font-semibold tracking-[0.12em] text-[#728075] uppercase"
                            >Private input</span
                        >
                    </div>
                    <div class="p-5 sm:p-6">
                        <label for="ticket-text" class="sr-only"
                            >Support ticket text</label
                        >
                        <textarea
                            id="ticket-text"
                            v-model="sourceText"
                            @input="updateText"
                            maxlength="12000"
                            placeholder="Paste a support ticket here…"
                            class="min-h-[245px] w-full resize-y rounded-xl border border-[#dfe5dd] bg-[#fbfcfa] p-4 text-sm leading-6 text-[#344238] transition outline-none placeholder:text-[#a0aaa1] focus:border-[#82a58a] focus:bg-white focus:ring-4 focus:ring-[#eef5ef]"
                        ></textarea>
                        <div
                            class="mt-2 flex justify-between text-[11px] text-[#9aa39b]"
                        >
                            <span>Max 12,000 characters</span>
                            <span
                                >{{ sourceText.length.toLocaleString() }} /
                                12,000</span
                            >
                        </div>

                        <div class="mt-6">
                            <div class="mb-3 flex items-center justify-between">
                                <p
                                    class="text-xs font-semibold tracking-[0.12em] text-[#69776d] uppercase"
                                >
                                    Sample tickets
                                </p>
                                <span class="text-[11px] text-[#a1aaa2]"
                                    >Fictional data</span
                                >
                            </div>
                            <div class="grid gap-2 sm:grid-cols-2">
                                <button
                                    v-for="(
                                        ticket, index
                                    ) in props.sampleTickets"
                                    :key="ticket.id"
                                    type="button"
                                    @click="loadSample(ticket)"
                                    class="group flex min-h-[60px] items-center gap-3 rounded-lg border px-3 text-left transition"
                                    :class="
                                        activeSample === ticket.id
                                            ? 'border-[#9bb7a0] bg-[#f4f8f4]'
                                            : 'border-[#edf0eb] bg-white hover:border-[#cbd9cc] hover:bg-[#fbfcfa]'
                                    "
                                >
                                    <span
                                        class="flex size-7 shrink-0 items-center justify-center rounded-md bg-[#f1f4ef] text-[10px] font-bold text-[#718074]"
                                        >0{{ index + 1 }}</span
                                    >
                                    <span
                                        class="line-clamp-2 text-xs leading-5 text-[#56645a]"
                                        >{{ ticket.title }}</span
                                    >
                                    <svg
                                        viewBox="0 0 20 20"
                                        fill="none"
                                        class="ml-auto size-4 shrink-0 text-[#9ba89d] group-hover:text-[#4f7858]"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="m7.5 4.5 5.5 5.5-5.5 5.5"
                                            stroke="currentColor"
                                            stroke-width="1.5"
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                        />
                                    </svg>
                                </button>
                                <p
                                    v-if="props.sampleTickets.length === 0"
                                    class="col-span-2 rounded-lg border border-dashed border-[#dfe5dd] px-3 py-4 text-center text-xs text-[#89948b]"
                                >
                                    Run
                                    <code
                                        class="rounded bg-[#f2f4f0] px-1 py-0.5"
                                        >php artisan db:seed</code
                                    >
                                    to load the demo tickets.
                                </p>
                            </div>
                        </div>

                        <div
                            v-if="errorMessage"
                            role="alert"
                            class="mt-5 rounded-lg border border-[#f1d9d3] bg-[#fff8f6] px-4 py-3 text-sm text-[#a34b3d]"
                        >
                            {{ errorMessage }}
                        </div>

                        <button
                            type="button"
                            @click="redactTicket"
                            :disabled="
                                isSubmitting || sourceText.trim().length < 8
                            "
                            class="mt-6 flex w-full items-center justify-center gap-2 rounded-xl bg-[#173d2b] px-5 py-3.5 text-sm font-semibold text-white shadow-[0_4px_10px_rgba(24,61,43,0.13)] transition hover:bg-[#214d36] disabled:cursor-not-allowed disabled:bg-[#9ba99e] disabled:shadow-none"
                        >
                            <svg
                                v-if="isSubmitting"
                                viewBox="0 0 24 24"
                                fill="none"
                                class="size-4 animate-spin"
                                aria-hidden="true"
                            >
                                <circle
                                    cx="12"
                                    cy="12"
                                    r="9"
                                    stroke="currentColor"
                                    stroke-opacity=".25"
                                    stroke-width="3"
                                />
                                <path
                                    d="M21 12a9 9 0 0 0-9-9"
                                    stroke="currentColor"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <svg
                                v-else
                                viewBox="0 0 24 24"
                                fill="none"
                                class="size-4"
                                aria-hidden="true"
                            >
                                <path
                                    d="M12 3v18M3 12h18"
                                    stroke="currentColor"
                                    stroke-width="1.8"
                                    stroke-linecap="round"
                                />
                            </svg>
                            {{
                                isSubmitting
                                    ? 'Masking identifiers…'
                                    : 'Redact & summarize'
                            }}
                        </button>
                        <p
                            class="mt-3 text-center text-[11px] leading-5 text-[#929d93]"
                        >
                            Email · phone · Harbor Desk order ID are masked
                            before summary generation.
                        </p>
                    </div>
                </section>

                <section
                    class="overflow-hidden rounded-2xl border border-[#e1e7df] bg-white shadow-[0_8px_30px_rgba(28,48,34,0.04)]"
                >
                    <div
                        class="flex items-center justify-between border-b border-[#edf0eb] px-5 py-4 sm:px-6"
                    >
                        <div>
                            <p class="text-sm font-semibold">
                                Redaction review
                            </p>
                            <p class="mt-1 text-xs text-[#849087]">
                                Check the safe version before using the summary
                            </p>
                        </div>
                        <span
                            v-if="result"
                            class="flex items-center gap-1.5 rounded-full bg-[#eff7f0] px-2.5 py-1 text-[10px] font-semibold tracking-wide text-[#3d754b] uppercase"
                        >
                            <span
                                class="size-1.5 rounded-full bg-[#57a069]"
                            ></span>
                            Complete
                        </span>
                        <span
                            v-else
                            class="rounded-md bg-[#f4f6f3] px-2 py-1 text-[10px] font-semibold tracking-[0.12em] text-[#859087] uppercase"
                            >Awaiting ticket</span
                        >
                    </div>

                    <div v-if="result" class="space-y-5 p-5 sm:p-6">
                        <div class="grid gap-3 sm:grid-cols-2">
                            <div
                                class="rounded-xl border border-[#edf0eb] bg-[#fbfcfa] p-4"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[11px] font-semibold tracking-[0.1em] text-[#849087] uppercase"
                                    >
                                        Original
                                    </p>
                                    <span class="text-[10px] text-[#a0aaa1]"
                                        >{{
                                            result.source_text.length
                                        }}
                                        chars</span
                                    >
                                </div>
                                <p
                                    class="max-h-[220px] overflow-auto text-xs leading-5 break-words whitespace-pre-wrap text-[#68756b]"
                                >
                                    {{ result.source_text }}
                                </p>
                            </div>
                            <div
                                class="rounded-xl border border-[#cfe0d0] bg-[#f7faf6] p-4"
                            >
                                <div
                                    class="mb-3 flex items-center justify-between"
                                >
                                    <p
                                        class="text-[11px] font-semibold tracking-[0.1em] text-[#4d7655] uppercase"
                                    >
                                        Redacted
                                    </p>
                                    <span class="text-[10px] text-[#849b88]"
                                        >{{
                                            result.redacted_text.length
                                        }}
                                        chars</span
                                    >
                                </div>
                                <p
                                    class="max-h-[220px] overflow-auto text-xs leading-5 break-words whitespace-pre-wrap text-[#344e39]"
                                >
                                    {{ result.redacted_text }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="rounded-xl border border-[#edf0eb] px-4 py-4"
                        >
                            <div
                                class="flex flex-wrap items-center justify-between gap-3"
                            >
                                <div>
                                    <p class="text-sm font-semibold">
                                        Detected identifiers
                                    </p>
                                    <p class="mt-1 text-xs text-[#849087]">
                                        {{ entityCount }} replacement{{
                                            entityCount === 1 ? '' : 's'
                                        }}
                                        applied
                                    </p>
                                </div>
                                <span
                                    class="rounded-md bg-[#f1f4ef] px-2 py-1 text-[10px] font-medium text-[#718074]"
                                    >No original values in metadata</span
                                >
                            </div>
                            <div
                                v-if="result.entities.length"
                                class="mt-3 flex flex-wrap gap-2"
                            >
                                <span
                                    v-for="entity in result.entities"
                                    :key="entity.type"
                                    class="inline-flex items-center gap-1.5 rounded-full border border-[#dce8dd] bg-[#f4f8f3] px-2.5 py-1 text-xs font-medium text-[#456b4c]"
                                >
                                    {{ entity.type }}
                                    <span class="text-[#829784]"
                                        >×{{ entity.count }}</span
                                    >
                                </span>
                                <span
                                    v-for="placeholder in result.entities.flatMap(
                                        (entity) => entity.placeholders,
                                    )"
                                    :key="placeholder"
                                    class="rounded-md bg-[#f3f5f1] px-2 py-1 font-mono text-[10px] text-[#68786a]"
                                    >{{ placeholder }}</span
                                >
                            </div>
                            <p v-else class="mt-3 text-xs text-[#849087]">
                                No supported identifiers were found in this
                                ticket.
                            </p>
                        </div>

                        <div
                            class="rounded-xl border border-[#e0e8df] bg-[#f7faf6] p-4"
                        >
                            <div class="flex items-start gap-3">
                                <span
                                    class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-white text-[#477250] shadow-sm"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        class="size-4"
                                        aria-hidden="true"
                                    >
                                        <path
                                            d="M12 3.5v2m0 13v2M3.5 12h2m13 0h2M5.99 5.99l1.42 1.42m9.18 9.18 1.42 1.42m0-12.02-1.42 1.42m-9.18 9.18-1.42 1.42"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                            stroke-linecap="round"
                                        />
                                        <circle
                                            cx="12"
                                            cy="12"
                                            r="4"
                                            stroke="currentColor"
                                            stroke-width="1.6"
                                        />
                                    </svg>
                                </span>
                                <div class="min-w-0 flex-1">
                                    <div
                                        class="flex flex-wrap items-center gap-2"
                                    >
                                        <p class="text-sm font-semibold">
                                            Ticket summary
                                        </p>
                                        <span
                                            class="rounded bg-white px-1.5 py-0.5 text-[9px] font-semibold tracking-[0.1em] text-[#62806a] uppercase"
                                            >{{ props.summaryMode }}</span
                                        >
                                    </div>
                                    <p
                                        v-if="result.summary"
                                        class="mt-2 text-sm leading-6 text-[#536357]"
                                    >
                                        {{ result.summary }}
                                    </p>
                                    <p
                                        v-else
                                        class="mt-2 text-sm leading-6 text-[#7c887e]"
                                    >
                                        Redaction succeeded. A summary is
                                        temporarily unavailable; the masked
                                        ticket above is ready to review.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        v-else
                        class="flex min-h-[510px] flex-col items-center justify-center px-8 py-12 text-center"
                    >
                        <div
                            class="relative mb-5 flex size-[76px] items-center justify-center rounded-[22px] border border-[#e3eae1] bg-[#f6f9f5] text-[#66826a]"
                        >
                            <svg
                                viewBox="0 0 32 32"
                                fill="none"
                                class="size-9"
                                aria-hidden="true"
                            >
                                <rect
                                    x="5.5"
                                    y="8"
                                    width="21"
                                    height="16"
                                    rx="3"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                />
                                <path
                                    d="M6.5 10 16 17l9.5-7"
                                    stroke="currentColor"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                                <path
                                    d="m21.5 5.5 2 2m3-4 2 2"
                                    stroke="#94ae96"
                                    stroke-width="1.5"
                                    stroke-linecap="round"
                                />
                            </svg>
                            <span
                                class="absolute -top-1 -right-1 size-3 rounded-full border-2 border-white bg-[#a8c3aa]"
                            ></span>
                        </div>
                        <p
                            class="text-base font-semibold tracking-tight text-[#35463a]"
                        >
                            Your review appears here
                        </p>
                        <p
                            class="mt-2 max-w-[290px] text-sm leading-6 text-[#879289]"
                        >
                            Choose a sample or paste a ticket. We’ll show every
                            replacement and the summary side by side.
                        </p>
                        <div
                            class="mt-7 flex items-center gap-2 text-[11px] text-[#98a299]"
                        >
                            <svg
                                viewBox="0 0 20 20"
                                fill="none"
                                class="size-4"
                                aria-hidden="true"
                            >
                                <path
                                    d="M10 2.5 4.5 5v4c0 3.6 2.3 6.5 5.5 7.8 3.2-1.3 5.5-4.2 5.5-7.8V5L10 2.5Z"
                                    stroke="currentColor"
                                    stroke-width="1.3"
                                />
                                <path
                                    d="m7.7 9.8 1.5 1.5 3.1-3.1"
                                    stroke="currentColor"
                                    stroke-width="1.3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                            Deterministic masking runs before AI
                        </div>
                    </div>
                </section>
            </div>

            <footer
                class="mt-8 flex flex-col justify-between gap-2 border-t border-[#e3e8e1] pt-5 text-[11px] text-[#99a29a] sm:flex-row sm:items-center"
            >
                <p>
                    Harbor Desk is a fictional demo. Use synthetic support data
                    only.
                </p>
                <p>
                    PII Redact Desk
                    <span class="mx-1 text-[#c5ccc5]">·</span> Redact locally
                    before AI
                </p>
            </footer>
        </main>
    </div>
</template>
