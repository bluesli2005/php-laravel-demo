<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { apiWritesEnabled, ApiError } from '../api/client';
import { createLifeInsurancePolicy } from '../api/lifeInsurancePolicies';
import LifeInsurancePolicyForm from '../components/lifeInsurancePolicyForm.vue';
import { emptyPolicyPayload, type LifeInsurancePolicyPayload } from '../types/lifeInsurancePolicy';

const router = useRouter();
const form = ref<InstanceType<typeof LifeInsurancePolicyForm> | null>(null);
const saving = ref(false);

async function save(payload: LifeInsurancePolicyPayload): Promise<void> {
    saving.value = true;

    try {
        const policy = await createLifeInsurancePolicy(payload);
        await router.push(`/policies/${policy.id}`);
    } catch (error) {
        if (error instanceof ApiError && error.status === 403) {
            form.value?.applyApiError(new Error('当前环境禁止写入保单。'));
        } else {
            form.value?.applyApiError(error);
        }
    } finally {
        saving.value = false;
    }
}
</script>

<template>
    <section class="grid gap-6">
        <div><p class="text-sm font-semibold text-sky-700">生命保险 Demo</p><h1 class="text-4xl font-semibold tracking-tight text-slate-950">新建保单</h1></div>
        <LifeInsurancePolicyForm ref="form" :initial-value="emptyPolicyPayload()" :saving="saving" :writes-enabled="apiWritesEnabled" @cancel="router.push('/policies')" @submit="save" />
    </section>
</template>