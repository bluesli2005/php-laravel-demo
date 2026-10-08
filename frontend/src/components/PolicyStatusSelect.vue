<script setup lang="ts">
import { policyStatusLabels, policyStatuses, type PolicyStatus } from '../types/lifeInsurancePolicy';
import BaseSelect from './BaseSelect.vue';

defineProps<{
    modelValue: PolicyStatus | '';
}>();

const emit = defineEmits<{
    'update:modelValue': [value: PolicyStatus | ''];
}>();

const options = [
    { value: '', label: '全部' },
    ...policyStatuses.map((status) => ({
        value: status,
        label: policyStatusLabels[status],
    })),
];
</script>

<template>
    <label class="grid gap-2 text-sm font-semibold text-slate-700" for="policy-status">
        保险状态
        <BaseSelect
            id="policy-status"
            :model-value="modelValue"
            :options="options"
            @update:model-value="emit('update:modelValue', $event as PolicyStatus | '')"
        />
    </label>
</template>
