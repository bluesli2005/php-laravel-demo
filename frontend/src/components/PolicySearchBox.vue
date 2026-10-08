<script setup lang="ts">
import BaseInput from './BaseInput.vue';

defineProps<{
    modelValue: string;
    placeholder?: string;
}>();

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <label class="grid gap-2 text-sm font-semibold text-slate-700" for="policy-search">
        关键词
        <BaseInput
            id="policy-search"
            :model-value="modelValue"
            :placeholder="placeholder ?? '保单号、投保人、被保险人、受益人或备注'"
            type="search"
            @update:model-value="emit('update:modelValue', $event)"
        />
    </label>
</template>
