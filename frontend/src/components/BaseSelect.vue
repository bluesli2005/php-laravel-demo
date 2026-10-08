<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: string;
    id: string;
    options: Array<{
        value: string;
        label: string;
    }>;
    ariaLabel?: string;
}>(), {
    ariaLabel: '请选择',
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <select
        :id="id"
        :value="modelValue"
        :aria-label="ariaLabel"
        class="form-input"
        @change="emit('update:modelValue', ($event.target as HTMLSelectElement).value)"
    >
        <option v-for="option in options" :key="option.value" :value="option.value">
            {{ option.label }}
        </option>
    </select>
</template>
