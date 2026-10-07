<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { ApiError } from '../api/client';
import { getWelcomeMessage } from '../api/welcomeMessages';
import type { ContentState, PageName, WelcomeMessage } from '../types/welcomeMessage';
import ContentStateDisplay from './ContentState.vue';

const props = defineProps<{
    page: PageName;
    title: string;
}>();

const state = ref<ContentState>('loading');
const message = ref<WelcomeMessage | null>(null);
const errorMessage = ref('');
let activeRequest: AbortController | null = null;

onMounted(loadMessage);
onBeforeUnmount(() => activeRequest?.abort());

async function loadMessage(): Promise<void> {
    activeRequest?.abort();

    const request = new AbortController();
    activeRequest = request;
    state.value = 'loading';
    message.value = null;
    errorMessage.value = '';

    try {
        const response = await getWelcomeMessage(props.page, request.signal);

        if (request.signal.aborted) {
            return;
        }

        message.value = response;
        state.value = response.content.trim() === '' ? 'empty' : 'ready';
    } catch (error) {
        if (request.signal.aborted) {
            return;
        }

        if (error instanceof ApiError && error.status === 404) {
            state.value = 'empty';
            return;
        }

        errorMessage.value = describeError(error);
        state.value = 'error';
    } finally {
        if (activeRequest === request) {
            activeRequest = null;
        }
    }
}

function describeError(error: unknown): string {
    if (error instanceof ApiError && error.status === 0) {
        return 'The API is unavailable. Check that the Laravel server is running.';
    }

    if (error instanceof TypeError) {
        return 'The API returned data in an unexpected format.';
    }

    if (error instanceof Error && error.message !== '') {
        return error.message;
    }

    return 'The page content could not be loaded.';
}
</script>

<template>
    <section class="grid gap-6">
        <h1 class="text-4xl font-semibold tracking-tight text-slate-950">{{ title }}</h1>

        <ContentStateDisplay :state="state" :error-message="errorMessage">
            <template #empty>No content is available for this page.</template>

            <template #error="{ message: displayedError }">
                <div class="grid gap-4">
                    <p>{{ displayedError }}</p>
                    <div>
                        <button
                            class="rounded-md bg-red-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700"
                            type="button"
                            @click="loadMessage"
                        >
                            Try again
                        </button>
                    </div>
                </div>
            </template>

            <p class="text-lg leading-8 text-slate-700">{{ message?.content }}</p>
        </ContentStateDisplay>
    </section>
</template>
