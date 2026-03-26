<template>
    <div class="mx-auto w-full max-w-5xl px-4 py-10 sm:px-6 sm:py-14">
        <section class="rounded-2xl border border-zinc-700/40 bg-zinc-900/70 p-6 shadow-2xl shadow-black/30 sm:p-8">
            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-rose-300">
                Vacancy Hub
            </p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-white sm:text-4xl">
                Поиск работы без шума и мусора
            </h1>
            <p class="mt-3 max-w-3xl text-sm leading-relaxed text-zinc-300 sm:text-base">
                Агрегатор вакансий с акцентом на
                <span class="text-rose-300">реальные предложения</span>
                и
                <span class="text-rose-300">прозрачную аналитику</span>.
                Никакой
                <span class="text-rose-300">лишней рекламы</span>
                и бесполезных "курсов" внутри поиска. Помогает
                <span class="text-rose-300">быстрее найти подходящий оффер</span>
                за счет точных фильтров и приоритета релевантных вакансий.
            </p>

            <div class="mt-6">
                <router-link
                    to="/search"
                    class="inline-flex items-center rounded-2xl bg-rose-600 px-7 py-4 text-base font-semibold text-white transition hover:bg-rose-500"
                >
                    Начать поиск вакансий
                </router-link>
            </div>
        </section>

        <section class="mt-6 grid gap-4 sm:grid-cols-2">
            <article class="rounded-xl border border-zinc-700/35 bg-zinc-900/65 p-5">
                <h2 class="text-lg font-semibold text-zinc-100">Без рекламы и лишнего</h2>
                <p class="mt-2 text-sm text-zinc-400">
                    Только
                    <span class="text-rose-300">вакансии</span>
                    и полезные инструменты для поиска работы. Никаких баннеров и навязчивых
                    блоков.
                </p>
            </article>

            <article class="rounded-xl border border-rose-500/30 bg-zinc-900/65 p-5">
                <h2 class="text-lg font-semibold text-zinc-100">Аналитика вакансий</h2>
                <p class="mt-2 text-sm text-zinc-400">
                    Смотри структуру рынка:
                    <span class="text-rose-300">требования</span>,
                    <span class="text-rose-300">зарплаты</span>,
                    динамику публикаций и актуальность предложений.
                </p>
            </article>

            <article class="rounded-xl border border-zinc-700/35 bg-zinc-900/65 p-5">
                <h2 class="text-lg font-semibold text-zinc-100">Оценка ваших шансов</h2>
                <p class="mt-2 text-sm text-zinc-400">
                    Показываем
                    <span class="text-rose-300">что подходит сейчас</span>
                    и что стоит подтянуть для лучших откликов.
                </p>
            </article>

            <article class="rounded-xl border border-zinc-700/35 bg-zinc-900/65 p-5">
                <h2 class="text-lg font-semibold text-zinc-100">Фильтры и алерты</h2>
                <p class="mt-2 text-sm text-zinc-400">
                    <span class="text-rose-300">Гибкие фильтры</span>
                    и
                    <span class="text-rose-300">Telegram-алерты</span>
                    по подходящим вакансиям.
                </p>
            </article>
        </section>

        <section class="mt-6 rounded-xl border border-rose-500/30 bg-zinc-900/65 p-5">
            <p class="text-xs uppercase tracking-[0.12em] text-rose-300">Прозрачность</p>
            <h2 class="text-lg font-semibold text-zinc-100">Оценка работодателей</h2>
            <p class="mt-2 text-sm text-zinc-400">
                Формируем более прозрачную картину работодателя на основе вакансий и их
                стабильности:
                <span class="text-rose-300">что публикуют</span>,
                <span class="text-rose-300">как меняют требования</span>
                и на что реально делают акцент.
            </p>
        </section>
    </div>
</template>
