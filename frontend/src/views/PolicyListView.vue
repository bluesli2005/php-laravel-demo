<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ApiError } from '../api/client';
import { getLifeInsurancePolicies } from '../api/lifeInsurancePolicies';
import BaseTable, { type TableColumn } from '../components/BaseTable.vue';
import ContentStateDisplay from '../components/ContentState.vue';
import PolicySearchBox from '../components/PolicySearchBox.vue';
import PolicyStatusSelect from '../components/PolicyStatusSelect.vue';
import { policyStatusLabels, type LifeInsurancePolicy, type PolicyStatus } from '../types/lifeInsurancePolicy';

const state = ref<'loading' | 'ready' | 'empty' | 'error'>('loading');
const policies = ref<LifeInsurancePolicy[]>([]);
const searchQuery = ref('');
const statusFilter = ref<PolicyStatus | ''>('');
const errorMessage = ref('');
let request: AbortController | null = null;

const columns: readonly TableColumn[] = [
    { key: 'policy_number', label: '保单号' },
    { key: 'policyholder_name', label: '投保人' },
    { key: 'insured_name', label: '被保险人' },
    { key: 'coverage_amount', label: '保额' },
    { key: 'status', label: '状态' },
    { key: 'effective_date', label: '生效日' },
    { key: 'actions', label: '操作' },
];

onMounted(loadPolicies);
onBeforeUnmount(() => request?.abort());

async function loadPolicies(): Promise<void> {
    request?.abort();
    request = new AbortController();
    state.value = 'loading';
    errorMessage.value = '';

    try {
        policies.value = await getLifeInsurancePolicies({
            search: searchQuery.value,
            status: statusFilter.value,
        }, request.signal);
        state.value = policies.value.length === 0 ? 'empty' : 'ready';
    } catch (error) {
        if (request.signal.aborted) return;
        errorMessage.value = error instanceof ApiError && error.status === 0
            ? 'API 不可用，请确认 Laravel 服务正在运行。'
            : error instanceof Error ? error.message : '保单列表加载失败。';
        state.value = 'error';
    }
}

function resetSearch(): void {
    searchQuery.value = '';
    statusFilter.value = '';
    loadPolicies();
}

function getPolicyId(row: object): number {
    return (row as LifeInsurancePolicy).id;
}

function formatPolicyAmount(row: object): string {
    const policy = row as LifeInsurancePolicy;

    return `${policy.currency} ${Number(policy.coverage_amount).toLocaleString('zh-CN', { minimumFractionDigits: 2 })}`;
}

function getPolicyStatus(value: unknown): string {
    return typeof value === 'string' && value in policyStatusLabels
        ? policyStatusLabels[value as PolicyStatus]
        : String(value ?? '');
}

function policyStatusClass(value: unknown): string {
    return value === 'active'
        ? 'bg-green-100 text-green-800'
        : value === 'cancelled'
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

        <form class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_12rem_auto_auto] sm:items-end" @submit.prevent="loadPolicies">
            <PolicySearchBox v-model="searchQuery" />
            <PolicyStatusSelect v-model="statusFilter" />
            <button class="button-primary" type="submit">搜索</button>
            <button class="button-secondary" type="button" @click="resetSearch">重置</button>
        </form>

        <ContentStateDisplay :state="state" :error-message="errorMessage">
            <template #empty>
                <div class="grid gap-3">
                    <p>{{ searchQuery.trim() !== '' || statusFilter !== '' ? '没有找到匹配的保单。' : '暂无保单。' }}</p>
                    <RouterLink v-if="searchQuery.trim() === '' && statusFilter === ''" class="button-primary w-fit" to="/policies/create">创建第一张保单</RouterLink>
                </div>
            </template>
            <template #error>
                <button class="button-secondary" type="button" @click="loadPolicies">重新加载</button>
            </template>
            <BaseTable v-if="policies.length > 0" :columns="columns" :rows="policies" row-key="id">
                <template #cell-policy_number="{ value, row }">
                    <RouterLink class="font-semibold text-sky-700" :to="`/policies/${getPolicyId(row)}`">{{ value }}</RouterLink>
                </template>
                <template #cell-coverage_amount="{ row }">
                    {{ formatPolicyAmount(row) }}
                </template>
                <template #cell-status="{ value }">
                    <span :class="['rounded-full px-2 py-1 text-xs font-semibold', policyStatusClass(value)]">{{ getPolicyStatus(value) }}</span>
                </template>
                <template #cell-actions="{ row }">
                    <RouterLink class="text-sky-700 hover:underline" :to="`/policies/${getPolicyId(row)}`">查看</RouterLink>
                </template>
            </BaseTable>
        </ContentStateDisplay>
    </section>
</template>
