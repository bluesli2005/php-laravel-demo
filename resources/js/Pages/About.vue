<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import SiteNav from '../Components/SiteNav.vue';

defineProps<{ welcomeMessage: string }>();
</script>

<template>
    <Head title="About" />

    <SiteNav />
    <main class="mx-auto max-w-2xl px-6 py-16">
        <h1 class="text-3xl font-semibold">About</h1>
        <p class="mt-4 text-gray-700">{{ welcomeMessage }}</p>
    </main>
</template>
