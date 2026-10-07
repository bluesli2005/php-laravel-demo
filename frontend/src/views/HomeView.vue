<script setup lang="ts">
import ContentState from '../components/ContentState.vue';
</script>

<template>
    <section class="grid gap-6">
        <div class="grid gap-2">
            <p class="text-sm font-semibold uppercase tracking-widest text-sky-700">Independent Vue SPA</p>
            <h1 class="text-4xl font-semibold tracking-tight text-slate-950">Home</h1>
        </div>
        <ContentState state="empty">
            <template #empty>Home content will be connected to the API in phase 3.</template>
        </ContentState>
    </section>
</template>
