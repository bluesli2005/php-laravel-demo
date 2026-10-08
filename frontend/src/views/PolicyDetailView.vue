<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import { ApiError } from '../api/client';
import { deleteLifeInsurancePolicy, getLifeInsurancePolicy } from '../api/lifeInsurancePolicies';
import ContentStateDisplay from '../components/ContentState.vue';
import { policyStatusLabels, type LifeInsurancePolicy } from '../types/lifeInsurancePolicy';

const route = useRoute();
const router = useRouter();
const state = ref<'loading' | 'ready' | 'error'>('loading');
const policy = ref<LifeInsurancePolicy | null>(null);
const errorMessage = ref('');
const deleting = ref(false);

onMounted(loadPolicy);

async function loadPolicy(): Promise<void> {
    try {
        policy.value = await getLifeInsurancePolicy(Number(route.params.id));
        state.value = 'ready';
    } catch (error) {
        errorMessage.value = error instanceof ApiError && error.status === 404 ? '保单不存在。' : '保单加载失败。';
        state.value = 'error';
    }
}

async function removePolicy(): Promise<void> {
    if (policy.value === null || !window.confirm(`确认删除保单 ${policy.value.policy_number}？`)) return;
    deleting.value = true;

    try {
        await deleteLifeInsurancePolicy(policy.value.id);
        await router.push('/policies');
    } catch (error) {
        errorMessage.value = error instanceof Error ? error.message : '删除保单失败。';
    } finally {
        deleting.value = false;
    }
}
</script>

<template>
    <section class="grid gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div><p class="text-sm font-semibold text-sky-700">生命保险 Demo</p><h1 class="text-4xl font-semibold tracking-tight text-slate-950">保单详情</h1></div>
            <RouterLink class="button-secondary" to="/policies">返回列表</RouterLink>
        </div>
        <ContentStateDisplay :state="state" :error-message="errorMessage">
            <div v-if="policy" class="grid gap-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <div class="flex flex-wrap items-center justify-between gap-4"><div><p class="text-sm text-slate-500">保单号</p><p class="text-2xl font-semibold">{{ policy.policy_number }}</p></div><span class="rounded-full bg-slate-100 px-3 py-1 text-sm font-semibold">{{ policyStatusLabels[policy.status] }}</span></div>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-slate-500">投保人</dt><dd>{{ policy.policyholder_name }}</dd></div>
                    <div><dt class="text-sm text-slate-500">被保险人</dt><dd>{{ policy.insured_name }}</dd></div>
                    <div><dt class="text-sm text-slate-500">出生日期</dt><dd>{{ policy.insured_birth_date }}</dd></div>
                    <div><dt class="text-sm text-slate-500">受益人</dt><dd>{{ policy.beneficiary_name || '未填写' }}</dd></div>
                    <div><dt class="text-sm text-slate-500">保额</dt><dd>{{ policy.currency }} {{ policy.coverage_amount }}</dd></div>
                    <div><dt class="text-sm text-slate-500">保费</dt><dd>{{ policy.currency }} {{ policy.premium_amount }}</dd></div>
                    <div><dt class="text-sm text-slate-500">生效日期</dt><dd>{{ policy.effective_date }}</dd></div>
                    <div><dt class="text-sm text-slate-500">失效日期</dt><dd>{{ policy.expiry_date || '未填写' }}</dd></div>
                </dl>
                <div><p class="text-sm text-slate-500">备注</p><p class="whitespace-pre-wrap">{{ policy.notes || '无' }}</p></div>
                <p v-if="errorMessage" class="rounded-md bg-red-50 p-3 text-sm text-red-800" role="alert">{{ errorMessage }}</p>
                <div class="flex flex-wrap gap-3">
                    <RouterLink class="button-primary" :to="`/policies/${policy.id}/edit`">编辑保单</RouterLink>
                    <button :disabled="deleting" class="button-danger" type="button" @click="removePolicy">{{ deleting ? '删除中…' : '删除保单' }}</button>
                </div>
            </div>
        </ContentStateDisplay>
    </section>
</template>