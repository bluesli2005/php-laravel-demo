<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { ApiError, apiWritesEnabled } from '../api/client';
import {
    createWelcomeMessage,
    deleteWelcomeMessage,
    getWelcomeMessage,
    updateWelcomeMessage,
} from '../api/welcomeMessages';
import {
    welcomeMessageContentMaxLength,
    type ContentState,
    type PageName,
    type WelcomeMessage,
} from '../types/welcomeMessage';
import ContentStateDisplay from './ContentState.vue';

const props = withDefaults(
    defineProps<{
        page: PageName;
        title: string;
        writesEnabled?: boolean;
    }>(),
    {
        writesEnabled: apiWritesEnabled,
    },
);

const state = ref<ContentState>('loading');
const message = ref<WelcomeMessage | null>(null);
const contentInput = ref('');
const errorMessage = ref('');
const fieldError = ref('');
const formError = ref('');
const statusMessage = ref('');
const isEditing = ref(false);
const isSaving = ref(false);
const isDeleting = ref(false);
const isConfirmingDelete = ref(false);
let activeRequest: AbortController | null = null;

onMounted(loadMessage);
onBeforeUnmount(() => activeRequest?.abort());

async function loadMessage(): Promise<void> {
    const request = startRequest();
    state.value = 'loading';
    message.value = null;
    isEditing.value = false;
    isConfirmingDelete.value = false;
    clearFeedback();

    try {
        const response = await getWelcomeMessage(props.page, request.signal);

        if (request.signal.aborted) {
            return;
        }

        message.value = response;
        contentInput.value = response.content;
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
        finishRequest(request);
    }
}

function beginCreate(): void {
    contentInput.value = '';
    isEditing.value = true;
    isConfirmingDelete.value = false;
    clearFeedback();
}

function beginEdit(): void {
    contentInput.value = message.value?.content ?? '';
    isEditing.value = true;
    isConfirmingDelete.value = false;
    clearFeedback();
}

function cancelEdit(): void {
    contentInput.value = message.value?.content ?? '';
    isEditing.value = false;
    clearFeedback();
}

async function saveContent(): Promise<void> {
    clearFeedback();

    if (!validateContent()) {
        return;
    }

    const request = startRequest();
    isSaving.value = true;

    try {
        const response = message.value
            ? await updateWelcomeMessage(props.page, contentInput.value, request.signal)
            : await createWelcomeMessage(props.page, contentInput.value, request.signal);

        if (request.signal.aborted) {
            return;
        }

        message.value = response;
        contentInput.value = response.content;
        state.value = 'ready';
        isEditing.value = false;
        statusMessage.value = 'Content saved.';
    } catch (error) {
        if (!request.signal.aborted) {
            showFormError(error);
        }
    } finally {
        isSaving.value = false;
        finishRequest(request);
    }
}

async function deleteContent(): Promise<void> {
    const request = startRequest();
    isDeleting.value = true;
    clearFeedback();

    try {
        await deleteWelcomeMessage(props.page, request.signal);

        if (request.signal.aborted) {
            return;
        }

        message.value = null;
        contentInput.value = '';
        state.value = 'empty';
        isEditing.value = false;
        isConfirmingDelete.value = false;
        statusMessage.value = 'Content deleted. You can recreate it below.';
    } catch (error) {
        if (!request.signal.aborted) {
            formError.value = describeActionError(error);
        }
    } finally {
        isDeleting.value = false;
        finishRequest(request);
    }
}

function validateContent(): boolean {
    if (contentInput.value.trim() === '') {
        fieldError.value = 'Content is required.';
        return false;
    }

    if (contentInput.value.length > welcomeMessageContentMaxLength) {
        fieldError.value = `Content must not be greater than ${welcomeMessageContentMaxLength} characters.`;
        return false;
    }

    return true;
}

function showFormError(error: unknown): void {
    const contentError = getApiFieldError(error, 'content');

    if (contentError !== null) {
        fieldError.value = contentError;
        return;
    }

    formError.value = getApiFieldError(error, 'page') ?? describeActionError(error);
}

function getApiFieldError(error: unknown, field: string): string | null {
    if (!(error instanceof ApiError) || !isRecord(error.details)) {
        return null;
    }

    const messages = error.details[field];

    return Array.isArray(messages) && typeof messages[0] === 'string' ? messages[0] : null;
}

function describeActionError(error: unknown): string {
    if (error instanceof ApiError && error.status === 403) {
        return 'Writing is disabled in this environment.';
    }

    return describeError(error);
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

function startRequest(): AbortController {
    activeRequest?.abort();

    const request = new AbortController();
    activeRequest = request;

    return request;
}

function finishRequest(request: AbortController): void {
    if (activeRequest === request) {
        activeRequest = null;
    }
}

function clearFeedback(): void {
    errorMessage.value = '';
    fieldError.value = '';
    formError.value = '';
    statusMessage.value = '';
}

function isRecord(value: unknown): value is Record<string, unknown> {
    return typeof value === 'object' && value !== null && !Array.isArray(value);
}
</script>

<template>
    <section class="grid gap-6">
        <h1 class="text-4xl font-semibold tracking-tight text-slate-950">{{ title }}</h1>

        <ContentStateDisplay :state="state" :error-message="errorMessage">
            <template #empty>
                <div class="grid gap-4">
                    <p>{{ statusMessage || 'No content is available for this page.' }}</p>
                    <div v-if="writesEnabled && !isEditing">
                        <button
                            class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700"
                            type="button"
                            @click="beginCreate"
                        >
                            Create content
                        </button>
                    </div>
                </div>
            </template>

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

            <div class="grid gap-5">
                <p class="text-lg leading-8 text-slate-700">{{ message?.content }}</p>

                <div v-if="writesEnabled && !isEditing" class="flex flex-wrap gap-3">
                    <button
                        class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700"
                        type="button"
                        @click="beginEdit"
                    >
                        Edit content
                    </button>
                    <button
                        class="rounded-md border border-red-300 bg-white px-4 py-2 text-sm font-semibold text-red-700 transition-colors hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700"
                        type="button"
                        @click="isConfirmingDelete = true"
                    >
                        Delete content
                    </button>
                </div>
            </div>
        </ContentStateDisplay>

        <form
            v-if="writesEnabled && isEditing"
            class="grid gap-5 rounded-xl border border-slate-200 bg-white p-6 shadow-sm"
            @submit.prevent="saveContent"
        >
            <div class="grid gap-2">
                <label :for="`${page}-content`" class="text-sm font-semibold text-slate-900">Page content</label>
                <textarea
                    :id="`${page}-content`"
                    v-model="contentInput"
                    :aria-describedby="fieldError ? `${page}-content-error ${page}-content-count` : `${page}-content-count`"
                    :aria-invalid="fieldError !== ''"
                    :maxlength="welcomeMessageContentMaxLength"
                    class="min-h-32 w-full rounded-md border border-slate-300 px-3 py-2 text-slate-900 shadow-sm outline-none transition focus:border-sky-600 focus:ring-2 focus:ring-sky-200 aria-invalid:border-red-500 aria-invalid:ring-red-100"
                    name="content"
                ></textarea>
                <div class="flex flex-wrap justify-between gap-2 text-sm">
                    <p v-if="fieldError" :id="`${page}-content-error`" class="text-red-700">{{ fieldError }}</p>
                    <span v-else></span>
                    <p :id="`${page}-content-count`" class="text-slate-500">
                        {{ contentInput.length }} / {{ welcomeMessageContentMaxLength }}
                    </p>
                </div>
            </div>

            <p v-if="formError" class="rounded-md bg-red-50 p-3 text-sm text-red-800" role="alert">
                {{ formError }}
            </p>

            <div class="flex flex-wrap gap-3">
                <button
                    :disabled="isSaving"
                    class="rounded-md bg-sky-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-sky-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-sky-700 disabled:cursor-not-allowed disabled:opacity-60"
                    type="submit"
                >
                    {{ isSaving ? 'Saving…' : message ? 'Save changes' : 'Create content' }}
                </button>
                <button
                    :disabled="isSaving"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600 disabled:cursor-not-allowed disabled:opacity-60"
                    type="button"
                    @click="cancelEdit"
                >
                    Cancel
                </button>
            </div>
        </form>

        <div
            v-if="writesEnabled && isConfirmingDelete"
            class="grid gap-4 rounded-xl border border-red-200 bg-red-50 p-5 text-red-900"
            role="alert"
        >
            <p>Delete this page content? The page will show an empty state until content is recreated.</p>
            <p v-if="formError" class="text-sm font-semibold">{{ formError }}</p>
            <div class="flex flex-wrap gap-3">
                <button
                    :disabled="isDeleting"
                    class="rounded-md bg-red-700 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-red-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-700 disabled:cursor-not-allowed disabled:opacity-60"
                    type="button"
                    @click="deleteContent"
                >
                    {{ isDeleting ? 'Deleting…' : 'Delete permanently' }}
                </button>
                <button
                    :disabled="isDeleting"
                    class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-semibold text-slate-700 transition-colors hover:bg-slate-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-slate-600 disabled:cursor-not-allowed disabled:opacity-60"
                    type="button"
                    @click="isConfirmingDelete = false"
                >
                    Cancel
                </button>
            </div>
        </div>

        <p v-if="statusMessage && state === 'ready'" class="text-sm font-medium text-emerald-700" aria-live="polite">
            {{ statusMessage }}
        </p>
    </section>
</template>
