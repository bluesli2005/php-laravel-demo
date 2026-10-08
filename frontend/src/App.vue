<script setup lang="ts">
import { RouterView } from 'vue-router';
import SiteNav from './components/SiteNav.vue';
</script>

<template>
    <div class="min-h-screen bg-slate-50 text-slate-900 antialiased">
        <SiteNav />
        <main class="mx-auto w-full max-w-3xl px-6 py-16">
            <RouterView />
        </main>
    </div>
</template>
