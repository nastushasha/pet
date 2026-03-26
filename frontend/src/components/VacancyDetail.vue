<script setup>
defineProps({
    detail: { type: Object, default: null },
    loading: Boolean,
    selectedId: { type: Number, default: null },
    showClose: { type: Boolean, default: false },
});

defineEmits(['close']);
</script>

<template>
    <div
        class="relative min-h-[300px] overflow-hidden rounded-xl border border-zinc-700/40 bg-zinc-900/90 p-5 shadow-2xl shadow-black/40 ring-1 ring-white/5 sm:p-6"
    >
        <!-- тонкая «вторая» рамка для объёма -->
        <div
            class="pointer-events-none absolute inset-0 rounded-xl bg-rose-500/[0.08] opacity-75"
            aria-hidden="true"
        />
        <button
            v-if="showClose"
            type="button"
            aria-label="Закрыть"
            class="absolute right-3 top-3 z-20 inline-flex h-8 w-8 items-center justify-center rounded-full border border-zinc-700/40 bg-zinc-900 text-zinc-200 hover:bg-zinc-800"
            @click="$emit('close')"
        >
            ✕
        </button>

        <div class="relative">
            <template v-if="selectedId == null">
                <div
                    class="flex min-h-[240px] flex-col items-center justify-center rounded-lg border-2 border-dashed border-zinc-700 bg-zinc-900 px-4 py-8 text-center"
                >
                    <p class="text-sm font-medium text-zinc-300">Выбери вакансию слева</p>
                    <p class="mt-1 text-xs text-zinc-500">Описание и параметры подгрузятся здесь</p>
                </div>
            </template>
            <template v-else-if="loading">
                <div class="flex min-h-[240px] flex-col items-center justify-center gap-3">
                    <span
                        class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-zinc-600 border-t-rose-500"
                    />
                    <p class="text-sm text-zinc-400">Загрузка карточки…</p>
                </div>
            </template>
            <template v-else-if="detail">
                <div
                    class="border-b border-white/10 pb-4"
                    :class="showClose ? 'pr-12' : ''"
                >
                    <h2 class="text-xl font-semibold leading-snug tracking-tight text-zinc-100 sm:text-2xl">
                        {{ detail.title }}
                    </h2>
                    <div class="mt-2">
                        <span
                            class="inline-flex rounded-lg border border-rose-400/35 bg-rose-400/15 px-3 py-1.5 text-xs font-bold uppercase tracking-wider text-rose-300"
                        >
                            hh.ru
                        </span>
                    </div>
                    <p class="mt-1.5 text-sm text-zinc-400">
                        {{ detail.company }} · {{ detail.city }}
                    </p>
                </div>
                <p
                    v-if="detail.details_status === 'pending'"
                    class="mt-3 rounded-md border border-zinc-700 bg-zinc-900 px-3 py-2 text-xs text-zinc-300"
                >
                    Детали вакансии догружаются. Пока показана сокращенная карточка.
                </p>
                <p
                    v-else-if="detail.details_status === 'failed'"
                    class="mt-3 rounded-md border border-rose-900/40 bg-rose-950/20 px-3 py-2 text-xs text-rose-300/90"
                >
                    Не удалось получить полные детали с HH API. Можно попробовать позже.
                </p>

                <p
                    v-if="detail.salary_label"
                    class="mt-4 inline-block rounded-lg border border-rose-400/35 bg-rose-400/15 px-3 py-1.5 text-sm font-semibold text-rose-300"
                >
                    {{ detail.salary_label }}
                </p>

                <dl
                    v-if="detail.meta?.length"
                    class="mt-5 space-y-3 rounded-xl border border-zinc-700/35 bg-zinc-900/80 p-4 text-sm"
                >
                    <div
                        v-for="row in detail.meta"
                        :key="row.label"
                        class="grid gap-0.5 sm:grid-cols-[minmax(0,11rem)_1fr] sm:gap-4"
                    >
                        <dt class="text-zinc-400">{{ row.label }}</dt>
                        <dd class="font-medium text-zinc-200">{{ row.value }}</dd>
                    </div>
                </dl>

                <p class="mt-5 text-sm leading-relaxed text-zinc-300">
                    {{ detail.description }}
                </p>
                <div v-if="detail.skills?.length" class="mt-4 flex flex-wrap gap-2">
                    <span
                        v-for="s in detail.skills"
                        :key="s"
                        class="rounded-md border border-rose-400/35 bg-rose-400/15 px-2.5 py-1 text-xs font-medium text-rose-300"
                    >
                        {{ s }}
                    </span>
                </div>
                <a
                    v-if="detail.url"
                    :href="detail.url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="mt-6 inline-flex items-center gap-2 rounded-lg bg-zinc-700 px-4 py-2.5 text-sm font-semibold text-white shadow-lg shadow-black/45 transition hover:bg-zinc-600"
                >
                    Открыть на hh.ru
                    <span class="text-rose-100" aria-hidden="true">↗</span>
                </a>
            </template>
            <template v-else>
                <p class="text-sm text-rose-300">Карточка не найдена</p>
            </template>
        </div>
    </div>
</template>
