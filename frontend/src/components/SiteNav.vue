<script setup lang="ts">
import { RouterLink } from 'vue-router';

const menuItems = [
    { label: 'Home', to: '/' },
    { label: '保单管理', to: '/policies' },
    { label: 'About', to: '/about' },
    { label: 'Services', to: '/services' },
    { label: 'Contact', to: '/contact' },
] as const;
</script>

<template>
    <header class="border-b border-slate-200 bg-white">
        <nav aria-label="Main navigation" class="mx-auto flex max-w-3xl flex-wrap gap-2 px-6 py-5 sm:gap-6">
            <RouterLink
                v-for="item in menuItems"
                :key="item.to"
                :to="item.to"
                class="rounded-md px-3 py-2 text-sm font-medium text-slate-600 transition-colors hover:bg-slate-100 hover:text-slate-950 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-600 aria-[current=page]:bg-sky-50 aria-[current=page]:text-sky-800"
            >
                {{ item.label }}
            </RouterLink>
        </nav>
    </header>
</template>
