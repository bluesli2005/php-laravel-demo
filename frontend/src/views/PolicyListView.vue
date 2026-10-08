<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { ApiError } from '../api/client';
import { getLifeInsurancePolicies } from '../api/lifeInsurancePolicies';
import BaseButton from '../components/BaseButton.vue';
import BaseTable, { type TableColumn } from '../components/BaseTable.vue';
import BaseSelect from '../components/BaseSelect.vue';
import ContentStateDisplay from '../components/ContentState.vue';
import PolicySearchBox from '../components/PolicySearchBox.vue';
import PolicyStatusSelect from '../components/PolicyStatusSelect.vue';
import { policyStatusLabels, type LifeInsurancePolicy, type PolicyStatus } from '../types/lifeInsurancePolicy';

const state = ref<'loading' | 'ready' | 'empty' | 'error'>('loading');
const policies = ref<LifeInsurancePolicy[]>([]);
const searchQuery = ref('');
const statusFilter = ref<PolicyStatus | ''>('');
const errorMessage = ref('');
const currentPage = ref(1);
const lastPage = ref(1);
const totalPolicies = ref(0);
const perPage = ref('10');
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
const perPageOptions = [
    { value: '10', label: '10 条' },
    { value: '20', label: '20 条' },
    { value: '30', label: '30 条' },
    { value: '50', label: '50 条' },
];

onMounted(loadPolicies);
onBeforeUnmount(() => request?.abort());

async function loadPolicies(page = 1): Promise<void> {
    request?.abort();
    request = new AbortController();
    state.value = 'loading';
    errorMessage.value = '';

    try {
        const result = await getLifeInsurancePolicies({
            search: searchQuery.value,
            status: statusFilter.value,
            page,
            perPage: Number(perPage.value),
        }, request.signal);
        policies.value = result.policies;
        currentPage.value = result.currentPage;
        lastPage.value = result.lastPage;
        totalPolicies.value = result.total;
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
    loadFirstPage();
}

function loadFirstPage(): void {
    void loadPolicies();
}

function changePage(page: number): void {
    if (page >= 1 && page <= lastPage.value && page !== currentPage.value) {
        loadPolicies(page);
    }
}

function changePerPage(value: string): void {
    perPage.value = value;
    loadFirstPage();
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

        <form class="grid gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:grid-cols-[1fr_12rem_8rem_auto_auto] sm:items-end" @submit.prevent="loadFirstPage">
            <PolicySearchBox v-model="searchQuery" />
            <PolicyStatusSelect v-model="statusFilter" />
            <label class="grid gap-2 text-sm font-semibold text-slate-700" for="policy-per-page">
                每页显示
                <BaseSelect id="policy-per-page" :model-value="perPage" :options="perPageOptions" @update:model-value="changePerPage" />
            </label>
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
                <button class="button-secondary" type="button" @click="loadFirstPage">重新加载</button>
            </template>
            <div v-if="policies.length > 0" class="grid gap-4">
                <BaseTable :columns="columns" :rows="policies" row-key="id">
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

                <nav v-if="lastPage > 1" aria-label="保单列表分页" class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-sm text-slate-600">第 {{ currentPage }} / {{ lastPage }} 页，共 {{ totalPolicies }} 条</p>
                    <div class="flex gap-2">
                        <BaseButton :disabled="currentPage === 1" variant="secondary" @click="changePage(currentPage - 1)">上一页</BaseButton>
                        <BaseButton :disabled="currentPage === lastPage" variant="secondary" @click="changePage(currentPage + 1)">下一页</BaseButton>
                    </div>
                </nav>
            </div>
        </ContentStateDisplay>
    </section>
</template>
