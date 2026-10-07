<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();

const menuItems: { label: string; href: string }[] = [
    { label: 'Home', href: '/' },
    { label: 'About', href: '/about' },
    { label: 'Services', href: '/services' },
    { label: 'Contact', href: '/contact' },
];
</script>

<template>
    <nav aria-label="Main navigation" class="mx-auto flex max-w-2xl gap-6 px-6 pt-8">
        <a
            v-for="item in menuItems"
            :key="item.href"
            :href="item.href"
            :aria-current="page.url === item.href ? 'page' : undefined"
            class="text-gray-700 hover:text-black aria-[current=page]:font-semibold"
        >
            {{ item.label }}
        </a>
    </nav>
</template>
