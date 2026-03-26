<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { useRouter } from 'vue-router';
import { api } from '../api';

const router = useRouter();

const areas = ref([]);
const selected = ref(new Set());
const selectedNamesMap = ref(new Map());
const loading = ref(true);
const error = ref(null);
const query = ref('');
let suggestTimer = null;

const filtered = computed(() => {
    const q = query.value.trim().toLowerCase();
    if (!q) return areas.value;
    return areas.value.filter((a) => a.name.toLowerCase().includes(q));
});

function toggle(id) {
    const row = areas.value.find((a) => a.id === id);
    if (selected.value.has(id)) {
        selected.value.delete(id);
        selectedNamesMap.value.delete(id);
    } else {
        selected.value.add(id);
        if (row?.name) selectedNamesMap.value.set(id, row.name);
    }
    selected.value = new Set(selected.value);
    selectedNamesMap.value = new Map(selectedNamesMap.value);
}

const selectedNames = computed(() => Array.from(selectedNamesMap.value.values()));

const cityEmojiMap = new Map([
    ['москва', '💼'],
    ['санкт-петербург', '🍄'],
    ['питер', '🍄'],
    ['spb', '🍄'],
    ['новосибирск', '🥶'],
    ['екатеринбург', '⚙️'],
    ['казань', '🐉'],
    ['нижний новгород', '🛶'],
    ['челябинск', '🧱'],
    ['омск', '🫠'],
    ['самара', '🛰️'],
    ['ростов-на-дону', '🌶️'],
    ['уфа', '🐝'],
    ['красноярск', '🪵'],
    ['пермь', '🎭'],
    ['воронеж', '🦫'],
    ['волгоград', '🗿'],
    ['краснодар', '🍉'],
    ['сочи', '🏄'],
    ['владивосток', '🦀'],
    ['калининград', '🧭'],
]);

function cityEmoji(name) {
    const key = String(name ?? '').trim().toLowerCase();
    return cityEmojiMap.get(key) ?? '🤌';
}

function go() {
    const ids = Array.from(selected.value);
    router.push({ name: 'vacancies', query: { areas: ids.join(',') } });
}

function clearSelection() {
    selected.value = new Set();
    selectedNamesMap.value = new Map();
}

async function loadPopular() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get('/cities/popular');
        areas.value = data.success ? data.data : [];
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Не удалось загрузить города';
        areas.value = [];
    } finally {
        loading.value = false;
    }
}

async function suggest() {
    const q = query.value.trim();
    if (q.length < 2) {
        await loadPopular();
        return;
    }

    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get('/cities/suggest', { params: { text: q } });
        areas.value = data.success ? data.data : [];
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Не удалось получить подсказки';
        areas.value = [];
    } finally {
        loading.value = false;
    }
}

watch(query, () => {
    if (suggestTimer) clearTimeout(suggestTimer);
    suggestTimer = setTimeout(suggest, 250);
});

onMounted(loadPopular);
</script>

<template>
    <div class="relative">
        <div
            class="pointer-events-none absolute inset-0 -z-10"
            style="
                background-image:
                    radial-gradient(ellipse 45% 26% at 22% 9%, rgba(220, 38, 38, 0.2), transparent 60%),
                    radial-gradient(ellipse 36% 22% at 78% 15%, rgba(220, 38, 38, 0.08), transparent 62%);
            "
            aria-hidden="true"
        />

        <div class="mx-auto max-w-3xl px-4 py-10 sm:px-6 sm:py-12">
            <div
                class="mb-6 rounded-2xl border border-zinc-700/40 bg-zinc-900/65 p-5 shadow-2xl shadow-black/30 backdrop-blur sm:p-6"
            >
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-300">
                    Vacancy Search 2026
                </p>
                <h2 class="mt-2 text-2xl font-bold tracking-tight text-white sm:text-3xl">
                    Выберите город для поиска работы
                </h2>
            </div>

            <div
                class="mt-6 rounded-2xl border border-zinc-700/40 bg-zinc-900/75 p-6 shadow-2xl shadow-black/35 ring-1 ring-white/5 backdrop-blur sm:p-8"
            >
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center">
                <div
                    class="flex w-full flex-wrap items-center gap-2 rounded-xl border border-zinc-700/40 bg-zinc-900 px-3 py-3 text-zinc-100 focus-within:ring-2 focus-within:ring-rose-500/35"
                >
                    <span
                        v-for="([id, name]) in Array.from(selectedNamesMap.entries())"
                        :key="id"
                        class="inline-flex items-center gap-2 rounded-lg bg-zinc-700 px-3 py-1.5 text-sm font-semibold text-white"
                    >
                        <span class="max-w-[14rem] truncate">{{ name }}</span>
                        <button
                            type="button"
                            class="rounded-md bg-black/30 px-2 py-0.5 text-xs hover:bg-black/45"
                            @click="toggle(id)"
                        >
                            ✕
                        </button>
                    </span>

                    <input
                        v-model="query"
                        type="text"
                        class="min-w-[10ch] flex-1 bg-transparent px-1 py-1 text-base text-zinc-100 placeholder:text-zinc-500 focus:outline-none"
                        placeholder="Начни вводить город…"
                    />
                </div>
            </div>

            <div v-if="loading" class="mt-7 text-sm text-zinc-400">Загрузка…</div>
            <div v-else-if="error" class="mt-7 text-sm text-rose-300">{{ error }}</div>

            <div v-else class="mt-7">
                <div class="mb-5 flex items-center justify-between text-xs text-zinc-400">
                    <span>Выбрано: {{ selected.size }}</span>
                    <button
                        type="button"
                        class="text-zinc-400 hover:text-zinc-100"
                        @click="clearSelection"
                    >
                        Сбросить
                    </button>
                </div>

                <div class="grid gap-10 sm:grid-cols-2">
                    <button
                        type="button"
                        v-for="a in filtered"
                        :key="a.id"
                        class="w-full rounded-xl border px-5 py-5 text-left text-lg font-semibold transition sm:py-6"
                        :class="
                            selected.has(a.id)
                                ? 'border-rose-500/75 bg-zinc-700 text-white shadow-lg shadow-black/45'
                                : 'border-zinc-700/40 bg-zinc-900 text-zinc-100 hover:border-zinc-600/45 hover:bg-zinc-800/80'
                        "
                        @click="toggle(a.id)"
                    >
                        <span class="inline-flex items-center gap-3">
                            <span aria-hidden="true" class="text-2xl leading-none">{{ cityEmoji(a.name) }}</span>
                            <span class="block min-w-0 truncate">{{ a.name }}</span>
                        </span>
                    </button>
                </div>

                <div class="mt-8 flex items-center justify-end gap-4">
                    <button
                        type="button"
                        class="w-full rounded-2xl bg-zinc-700 px-8 py-4 text-lg font-bold text-white shadow-xl shadow-black/45 hover:bg-zinc-600 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
                        :disabled="selected.size === 0"
                        @click="go"
                    >
                        Показать вакансии
                    </button>
                </div>
            </div>
            </div>
        </div>
    </div>
</template>

