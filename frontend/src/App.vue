<script setup>
// Layout shell
</script>

<template>
    <div class="relative min-h-screen">
        <div class="sticky top-0 z-20 border-b border-zinc-700/40 bg-zinc-950/60 px-4 py-2 backdrop-blur sm:px-6">
            <router-link
                to="/"
                class="inline-block text-xs font-medium tracking-[0.02em] text-zinc-400 transition hover:text-zinc-200"
            >
                Vacancy Hub
            </router-link>
        </div>

        <router-view />
    </div>
</template>