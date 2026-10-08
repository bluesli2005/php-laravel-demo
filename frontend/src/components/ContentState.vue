<script setup lang="ts">
import type { ContentState } from '../types/ui';

withDefaults(
    defineProps<{
        state: ContentState;
        errorMessage?: string;
    }>(),
    {
        errorMessage: 'The page content could not be loaded.',
    },
);
</script>

<template>
    <div v-if="state === 'loading'" aria-live="polite" aria-busy="true" class="grid gap-3" role="status">
        <span class="sr-only">Loading page content.</span>
        <div class="h-4 w-full animate-pulse rounded bg-slate-200"></div>
        <div class="h-4 w-3/4 animate-pulse rounded bg-slate-200"></div>
    </div>

    <div v-else-if="state === 'error'" class="rounded-lg border border-red-200 bg-red-50 p-5 text-red-900" role="alert">
        <slot name="error" :message="errorMessage">{{ errorMessage }}</slot>
    </div>

    <div v-else-if="state === 'empty'" class="rounded-lg border border-dashed border-slate-300 bg-white p-6 text-slate-600" role="status">
        <slot name="empty">No page content is available yet.</slot>
    </div>

    <div v-else>
        <slot />
    </div>
</template>
