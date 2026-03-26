<script setup>
defineProps({
    groups: { type: Array, default: () => [] },
    groupBy: { type: String, default: 'none' },
    viewedIds: { type: Array, default: () => [] },
    loading: Boolean,
    error: { type: String, default: null },
    selectedId: { type: Number, default: null },
});

defineEmits(['retry', 'select']);

const toneVariants = ['border-l-rose-400', 'border-l-sky-400', 'border-l-violet-400', 'border-l-teal-400', 'border-l-amber-400'];

function hashIndex(value, modulo) {
    let hash = 0;
    for (let i = 0; i < value.length; i += 1) {
        hash = (hash * 31 + value.charCodeAt(i)) >>> 0;
    }
    return hash % modulo;
}

function toneClass(group, groupBy) {
    const label = String(group?.label ?? '').toLowerCase();

    if (groupBy === 'experience') {
        if (label.includes('без опыта') || label.includes('intern') || label.includes('стаж')) return 'border-l-emerald-400';
        if (label.includes('1') || label.includes('3')) return 'border-l-sky-400';
        if (label.includes('3') || label.includes('6')) return 'border-l-violet-400';
        if (label.includes('более') || label.includes('6') || label.includes('senior') || label.includes('lead')) return 'border-l-rose-400';
    }

    if (groupBy === 'schedule') {
        if (label.includes('удал')) return 'border-l-teal-400';
        if (label.includes('офис') || label.includes('полный день')) return 'border-l-amber-400';
        if (label.includes('гибк')) return 'border-l-sky-400';
    }

    if (groupBy === 'employment') {
        if (label.includes('полная')) return 'border-l-sky-400';
        if (label.includes('частич')) return 'border-l-violet-400';
        if (label.includes('проект')) return 'border-l-amber-400';
        if (label.includes('стаж')) return 'border-l-emerald-400';
    }

    return toneVariants[hashIndex(label || 'default', toneVariants.length)];
}
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-zinc-700/35 bg-zinc-900/70 shadow-2xl shadow-black/25 ring-1 ring-white/5"
    >
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-400">
            <span
                class="inline-block h-5 w-5 animate-spin rounded-full border-2 border-zinc-600 border-t-rose-500"
            />
            <p class="mt-3">Загрузка…</p>
        </div>

        <div v-else-if="error" class="space-y-3 p-5 text-center">
            <p class="text-sm text-rose-300">{{ error }}</p>
            <button
                type="button"
                class="rounded-lg bg-stone-700 px-4 py-2 text-sm font-medium text-white shadow-md shadow-stone-950/40 transition hover:bg-stone-600"
                @click="$emit('retry')"
            >
                Повторить
            </button>
        </div>

        <div
            v-else-if="groups.length"
            class="x-scrollbar overflow-x-auto p-6 pb-7"
            style="scrollbar-width: thin; scrollbar-color: rgba(190, 24, 93, 0.45) rgba(39, 20, 18, 0.9)"
        >
            <div
                class="flex gap-0"
                :class="groups.length <= 3 ? 'w-full min-w-0' : 'min-w-max'"
            >
            <section
                v-for="(group, groupIdx) in groups"
                :key="group.key"
                class="border-r border-zinc-800/60 bg-transparent last:border-r-0"
                :class="groups.length <= 3 ? 'min-w-0 flex-1' : 'w-[420px] shrink-0'"
            >
                <div
                    class="flex items-center justify-between border-b border-zinc-800/80 bg-zinc-900/70 px-6 py-4"
                >
                    <h4 class="text-sm font-semibold uppercase tracking-wide text-rose-200/95">
                        {{ group.label }}
                    </h4>
                    <span class="text-xs text-zinc-400">{{ group.items.length }}</span>
                </div>

                <ul class="no-scrollbar max-h-[52vh] overflow-y-auto py-1">
                    <li v-for="v in group.items" :key="v.id">
                        <button
                            type="button"
                            class="group m-5 flex w-[calc(100%-2.5rem)] flex-col items-start gap-4 rounded-xl border border-zinc-700/50 border-l-4 bg-zinc-800/85 px-7 py-7 text-left text-base transition duration-150"
                            :class="
                                selectedId === v.id
                                    ? `border-rose-300/80 ${toneClass(group, groupBy)} bg-zinc-700/95`
                                    : `${toneClass(group, groupBy)} hover:border-zinc-700/70 hover:bg-zinc-800/80`
                            "
                            @click="$emit('select', v.id)"
                        >
                            <div class="flex w-full items-start justify-between gap-4">
                                <span class="text-[19px] font-semibold leading-[1.45] text-white">
                                    {{ v.title }}
                                </span>
                                <span
                                    class="mt-0.5 inline-flex shrink-0 items-center rounded-md px-2 py-0.5 text-xs font-semibold"
                                    :class="
                                        viewedIds.includes(v.id)
                                            ? 'bg-zinc-700/70 text-zinc-300'
                                            : 'bg-rose-500/20 text-rose-300'
                                    "
                                >
                                    {{ viewedIds.includes(v.id) ? 'Просмотрена' : 'Новая' }}
                                </span>
                            </div>
                            <span class="text-[15px] leading-[1.6] text-zinc-300">{{ v.company }} · {{ v.city }}</span>
                            <span
                                v-if="v.experience || v.employment || v.schedule"
                                class="text-[15px] leading-[1.6] text-zinc-300/90"
                            >
                                <template v-if="v.experience">{{ v.experience }}</template>
                                <template v-if="v.experience && (v.employment || v.schedule)"> · </template>
                                <template v-if="v.employment">{{ v.employment }}</template>
                                <template v-if="v.employment && v.schedule"> · </template>
                                <template v-if="v.schedule">{{ v.schedule }}</template>
                            </span>
                            <span v-if="v.salary" class="text-[15px] font-semibold leading-[1.6] text-rose-300">
                                {{ v.salary }}
                            </span>
                            <span v-if="v.published_at_label" class="text-sm leading-[1.6] text-zinc-300/80">
                                Опубликована: {{ v.published_at_label }}
                            </span>
                        </button>
                    </li>
                </ul>
            </section>
            </div>
        </div>

        <p v-else class="p-8 text-center text-sm leading-relaxed text-zinc-400">
            Пока пусто. На бэкенде:
            <code
                class="mt-1 block rounded-md border border-zinc-700/80 bg-[#17191d] px-2 py-1.5 text-xs text-zinc-300"
                >php artisan hh:sync-vacancies</code
            >
        </p>
    </div>
</template>
