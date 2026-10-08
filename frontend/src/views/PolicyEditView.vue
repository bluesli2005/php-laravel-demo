<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { apiWritesEnabled, ApiError } from '../api/client';
import { getLifeInsurancePolicy, updateLifeInsurancePolicy } from '../api/lifeInsurancePolicies';
import LifeInsurancePolicyForm from '../components/LifeInsurancePolicyForm.vue';
import { policyToPayload, type LifeInsurancePolicy, type LifeInsurancePolicyPayload } from '../types/lifeInsurancePolicy';

const route = useRoute();
const router = useRouter();
const policy = ref<LifeInsurancePolicy | null>(null);
const form = ref<InstanceType<typeof LifeInsurancePolicyForm> | null>(null);
const loading = ref(true);
const saving = ref(false);
const errorMessage = ref('');

onMounted(loadPolicy);

async function loadPolicy(): Promise<void> {
    try {
        policy.value = await getLifeInsurancePolicy(Number(route.params.id));
    } catch (error) {
        errorMessage.value = error instanceof ApiError && error.status === 404 ? '保单不存在。' : '保单加载失败。';
    } finally {
        loading.value = false;
    }
}

async function save(payload: LifeInsurancePolicyPayload): Promise<void> {
    saving.value = true;

    try {
        const updated = await updateLifeInsurancePolicy(Number(route.params.id), payload);
        await router.push(`/policies/${updated.id}`);
    } catch (error) {
        form.value?.applyApiError(error);
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <section class="grid gap-6">
        <div><p class="text-sm font-semibold text-sky-700">生命保险 Demo</p><h1 class="text-4xl font-semibold tracking-tight text-slate-950">编辑保单</h1></div>
        <p v-if="loading" class="text-slate-600">加载中…</p>
        <p v-else-if="!policy" class="rounded-md bg-red-50 p-4 text-red-800" role="alert">{{ errorMessage }}</p>
        <LifeInsurancePolicyForm v-else ref="form" :initial-value="policyToPayload(policy)" :saving="saving" :writes-enabled="apiWritesEnabled" @cancel="router.push(`/policies/${policy.id}`)" @submit="save" />
    </section>
</template>