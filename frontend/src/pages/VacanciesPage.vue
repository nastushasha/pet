<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { api } from '../api';
import VacancyList from '../components/VacancyList.vue';
import VacancyDetail from '../components/VacancyDetail.vue';

const route = useRoute();
const router = useRouter();

const vacancies = ref([]);
const loading = ref(true);
const error = ref(null);
const selectedId = ref(null);
const detail = ref(null);
const detailLoading = ref(false);
const detailOpen = ref(false);
const groupBy = ref('city');
const groupMenuOpen = ref(false);
const viewedIds = ref(new Set());
const pagination = ref({
    current_page: 1,
    last_page: 1,
    per_page: 500,
    total: 0,
});

const areaParam = computed(() => (typeof route.query.areas === 'string' ? route.query.areas : ''));
const pageParam = computed(() => {
    const raw = Number(route.query.page ?? 1);
    return Number.isFinite(raw) && raw > 0 ? Math.floor(raw) : 1;
});

function numberFromUnknown(value, fallback = 0) {
    if (Array.isArray(value)) {
        const firstNumber = value.map((item) => Number(item)).find((num) => Number.isFinite(num));
        return firstNumber ?? fallback;
    }
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : fallback;
}

function normalizePagination(meta) {
    return {
        current_page: Math.max(1, Math.floor(numberFromUnknown(meta?.current_page, 1))),
        last_page: Math.max(1, Math.floor(numberFromUnknown(meta?.last_page, 1))),
        per_page: Math.max(1, Math.floor(numberFromUnknown(meta?.per_page, 500))),
        total: Math.max(0, Math.floor(numberFromUnknown(meta?.total, 0))),
    };
}

const sortedVacancies = computed(() =>
    [...vacancies.value].sort((a, b) => String(a.title ?? '').localeCompare(String(b.title ?? ''), 'ru'))
);

const groupedVacancies = computed(() => {
    if (groupBy.value === 'none') {
        return [{ key: 'all', label: 'Все вакансии', items: sortedVacancies.value }];
    }

    const map = new Map();
    for (const vacancy of sortedVacancies.value) {
        const raw = vacancy[groupBy.value];
        const label = raw ? String(raw) : 'Без значения';
        if (!map.has(label)) map.set(label, []);
        map.get(label).push(vacancy);
    }

    return Array.from(map.entries())
        .sort(([a], [b]) => a.localeCompare(b, 'ru'))
        .map(([label, items]) => ({ key: label, label, items }));
});

const groupOptions = [
    { value: 'city', label: 'По городу' },
    { value: 'company', label: 'По компании' },
    { value: 'experience', label: 'По опыту' },
    { value: 'employment', label: 'По занятости' },
    { value: 'schedule', label: 'По графику' },
];

const groupByLabel = computed(
    () => groupOptions.find((option) => option.value === groupBy.value)?.label ?? 'По городу'
);

const VIEWED_STORAGE_KEY = 'vacancy.viewed_ids.v1';

function loadViewedIds() {
    try {
        const raw = localStorage.getItem(VIEWED_STORAGE_KEY);
        if (!raw) return;
        const parsed = JSON.parse(raw);
        if (!Array.isArray(parsed)) return;
        viewedIds.value = new Set(parsed.map((x) => Number(x)).filter((x) => Number.isFinite(x)));
    } catch {
        viewedIds.value = new Set();
    }
}

function persistViewedIds() {
    localStorage.setItem(VIEWED_STORAGE_KEY, JSON.stringify(Array.from(viewedIds.value)));
}

async function loadList() {
    loading.value = true;
    error.value = null;
    try {
        const { data } = await api.get('/vacancies', {
            params: {
                ...(areaParam.value ? { areas: areaParam.value } : {}),
                page: pageParam.value,
                per_page: 500,
            },
        });
        vacancies.value = data.success ? data.data : [];
        pagination.value = normalizePagination(data.meta);
    } catch (e) {
        error.value = e.response?.data?.message ?? 'Не удалось загрузить вакансии';
        vacancies.value = [];
        pagination.value = { current_page: 1, last_page: 1, per_page: 500, total: 0 };
    } finally {
        loading.value = false;
    }
}

async function selectVacancy(id) {
    selectedId.value = id;
    if (!viewedIds.value.has(id)) {
        viewedIds.value.add(id);
        viewedIds.value = new Set(viewedIds.value);
        persistViewedIds();
    }
    detailOpen.value = true;
    detail.value = null;
    detailLoading.value = true;
    try {
        const { data } = await api.get(`/vacancies/${id}`);
        detail.value = data.success ? data.data : null;
    } catch {
        detail.value = null;
    } finally {
        detailLoading.value = false;
    }
}

function closeDetail() {
    detailOpen.value = false;
}

watch(detailOpen, (isOpen) => {
    document.body.style.overflow = isOpen ? 'hidden' : '';
});

onBeforeUnmount(() => {
    document.body.style.overflow = '';
});

function backToRegions() {
    router.push({ name: 'search', query: route.query });
}

function setPage(nextPage) {
    router.push({
        name: 'vacancies',
        query: {
            ...route.query,
            page: String(nextPage),
        },
    });
}

onMounted(() => {
    loadViewedIds();
    loadList();
});
watch([areaParam, pageParam], () => {
    selectedId.value = null;
    detail.value = null;
    loadList();
});

watch(detailOpen, (isOpen) => {
    if (isOpen) groupMenuOpen.value = false;
});
</script>

<template>
    <div class="w-full px-4 py-6 sm:px-6">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <h2 class="text-lg font-semibold tracking-tight text-zinc-100">Вакансии</h2>
                <span class="text-xs text-zinc-400">
                    {{ pagination.total }} шт.
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="rounded-lg border border-zinc-700/40 bg-zinc-900/70 px-3 py-2 text-sm font-medium text-zinc-200 hover:bg-zinc-800/80"
                    @click="backToRegions"
                >
                    Регионы
                </button>
                <button
                    type="button"
                    aria-label="Обновить"
                    class="rounded-lg bg-zinc-700 px-3 py-2 text-sm font-semibold text-white hover:bg-zinc-600"
                    @click="loadList"
                >
                    <svg
                        class="h-5 w-5"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                    >
                        <path d="M21 12a9 9 0 1 1-2.64-6.36" />
                        <path d="M21 3v6h-6" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="mt-5">
            <div
                class="mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-zinc-700/35 bg-zinc-900/70 px-3 py-2"
            >
                <span class="text-xs text-zinc-400">Группировать</span>
                <div class="relative">
                    <button
                        type="button"
                        class="inline-flex min-w-[14rem] items-center justify-between rounded-lg border border-zinc-700/45 bg-zinc-800/85 px-3 py-2.5 text-sm font-semibold tracking-[0.01em] text-zinc-100 shadow-inner shadow-black/20 transition hover:border-zinc-600/60 hover:bg-zinc-700/90 focus:outline-none focus:ring-2 focus:ring-rose-400/30"
                        @click="groupMenuOpen = !groupMenuOpen"
                    >
                        <span>{{ groupByLabel }}</span>
                        <svg
                            class="h-4 w-4 text-rose-300 transition"
                            :class="groupMenuOpen ? 'rotate-180' : ''"
                            viewBox="0 0 20 20"
                            fill="currentColor"
                            aria-hidden="true"
                        >
                            <path
                                fill-rule="evenodd"
                                d="M5.23 7.21a.75.75 0 0 1 1.06.02L10 11.17l3.71-3.94a.75.75 0 1 1 1.08 1.04l-4.25 4.5a.75.75 0 0 1-1.08 0l-4.25-4.5a.75.75 0 0 1 .02-1.06Z"
                                clip-rule="evenodd"
                            />
                        </svg>
                    </button>

                    <div
                        v-if="groupMenuOpen"
                        class="absolute left-0 top-[calc(100%+0.4rem)] z-20 w-full overflow-hidden rounded-lg border border-zinc-700/55 bg-zinc-800 shadow-xl shadow-black/35"
                    >
                        <button
                            v-for="option in groupOptions"
                            :key="option.value"
                            type="button"
                            class="block w-full border-b border-white/5 px-3 py-2.5 text-left text-sm font-medium tracking-[0.01em] text-zinc-200 last:border-b-0 hover:bg-zinc-700/90"
                            :class="groupBy === option.value ? 'bg-zinc-700/90 font-semibold text-rose-200' : ''"
                            @click="
                                groupBy = option.value;
                                groupMenuOpen = false;
                            "
                        >
                            {{ option.label }}
                        </button>
                    </div>
                </div>
            </div>

            <VacancyList
                :groups="groupedVacancies"
                :group-by="groupBy"
                :viewed-ids="Array.from(viewedIds)"
                :loading="loading"
                :error="error"
                :selected-id="selectedId"
                @retry="loadList"
                @select="selectVacancy"
            />
        </div>

        <div class="mt-5 flex items-center justify-between">
            <p class="text-xs text-zinc-400">
                Страница {{ pagination.current_page }} из {{ pagination.last_page }}
            </p>
            <div class="flex items-center gap-2">
                <button
                    type="button"
                    aria-label="Предыдущая страница"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-zinc-700/40 bg-zinc-900/70 text-zinc-200 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="pagination.current_page <= 1 || loading"
                    @click="setPage(pagination.current_page - 1)"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M15 18l-6-6 6-6" />
                    </svg>
                </button>
                <button
                    type="button"
                    aria-label="Следующая страница"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-zinc-700/40 bg-zinc-900/70 text-zinc-200 disabled:cursor-not-allowed disabled:opacity-50"
                    :disabled="pagination.current_page >= pagination.last_page || loading"
                    @click="setPage(pagination.current_page + 1)"
                >
                    <svg
                        class="h-4 w-4"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        aria-hidden="true"
                    >
                        <path d="M9 6l6 6-6 6" />
                    </svg>
                </button>
            </div>
        </div>

        <transition
            enter-active-class="transition-opacity duration-150"
            leave-active-class="transition-opacity duration-150"
            enter-from-class="opacity-0"
            leave-to-class="opacity-0"
        >
            <div
                v-if="detailOpen"
                class="fixed inset-0 z-50 flex items-start justify-center bg-black/65 p-4 pt-10 backdrop-blur-[1px] sm:p-6 sm:pt-14"
                @click.self="closeDetail"
            >
                <div class="w-full max-w-5xl">
                    <div class="max-h-[86vh] overflow-y-auto no-scrollbar rounded-xl">
                        <VacancyDetail
                            :detail="detail"
                            :loading="detailLoading"
                            :selected-id="selectedId"
                            :show-close="true"
                            @close="closeDetail"
                        />
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>

