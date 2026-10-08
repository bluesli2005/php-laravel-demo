<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ApiError } from '../api/client';
import { getLifeInsurancePolicies } from '../api/lifeInsurancePolicies';
import ContentStateDisplay from '../components/ContentState.vue';
import { policyStatusLabels, type LifeInsurancePolicy, type PolicyStatus } from '../types/lifeInsurancePolicy';

const state = ref<'loading' | 'ready' | 'empty' | 'error'>('loading');
const policies = ref<LifeInsurancePolicy[]>([]);
const errorMessage = ref('');
let request: AbortController | null = null;

onMounted(loadPolicies);
onBeforeUnmount(() => request?.abort());

async function loadPolicies(): Promise<void> {
    request?.abort();
    request = new AbortController();
    state.value = 'loading';
    errorMessage.value = '';

    try {
        policies.value = await getLifeInsurancePolicies(request.signal);
        state.value = policies.value.length === 0 ? 'empty' : 'ready';
    } catch (error) {
        if (request.signal.aborted) return;
        errorMessage.value = error instanceof ApiError && error.status === 0
            ? 'API 不可用，请确认 Laravel 服务正在运行。'
            : error instanceof Error ? error.message : '保单列表加载失败。';
        state.value = 'error';
    }
}

function formatAmount(amount: string, currency: string): string {
    return `${currency} ${Number(amount).toLocaleString('zh-CN', { minimumFractionDigits: 2 })}`;
}

function statusClass(status: PolicyStatus): string {
    return status === 'active'
        ? 'bg-green-100 text-green-800'
        : status === 'cancelled'
            ? 'bg-red-100 text-red-800'
            : 'bg-slate-100 text-slate-700';
}
</script>

<template>
    <section class="grid gap-6">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-sm font-semibold text-sky-700">生命保险 Demo</p>
                <h1 class="text-4xl font-semibold tracking-tight text-slate-950">保单列表</h1>
            </div>
            <RouterLink class="button-primary" to="/policies/create">新建保单</RouterLink>
        </div>

        <ContentStateDisplay :state="state" :error-message="errorMessage">
            <template #empty>
                <div class="grid gap-3">
                    <p>暂无保单。</p>
                    <RouterLink class="button-primary w-fit" to="/policies/create">创建第一张保单</RouterLink>
                </div>
            </template>
            <template #error>
                <button class="button-secondary" type="button" @click="loadPolicies">重新加载</button>
            </template>
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-3">保单号</th>
                            <th class="px-4 py-3">投保人</th>
                            <th class="px-4 py-3">被保险人</th>
                            <th class="px-4 py-3">保额</th>
                            <th class="px-4 py-3">状态</th>
                            <th class="px-4 py-3">生效日</th>
                            <th class="px-4 py-3">操作</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr v-for="policy in policies" :key="policy.id">
                            <td class="px-4 py-3 font-semibold text-sky-700"><RouterLink :to="`/policies/${policy.id}`">{{ policy.policy_number }}</RouterLink></td>
                            <td class="px-4 py-3">{{ policy.policyholder_name }}</td>
                            <td class="px-4 py-3">{{ policy.insured_name }}</td>
                            <td class="px-4 py-3">{{ formatAmount(policy.coverage_amount, policy.currency) }}</td>
                            <td class="px-4 py-3"><span :class="['rounded-full px-2 py-1 text-xs font-semibold', statusClass(policy.status)]">{{ policyStatusLabels[policy.status] }}</span></td>
                            <td class="px-4 py-3">{{ policy.effective_date }}</td>
                            <td class="px-4 py-3"><RouterLink class="text-sky-700 hover:underline" :to="`/policies/${policy.id}`">查看</RouterLink></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </ContentStateDisplay>
    </section>
</template>