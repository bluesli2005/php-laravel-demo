<script setup lang="ts">
withDefaults(defineProps<{
  modelValue: string | null;
    id: string;
    placeholder?: string;
    type?: 'date' | 'number' | 'search' | 'text';
  maxlength?: number | string;
  min?: number | string;
  step?: number | string;
    ariaInvalid?: boolean;
}>(), {
    placeholder: '',
    type: 'text',
    maxlength: undefined,
    min: undefined,
    step: undefined,
    ariaInvalid: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <input
        :id="id"
    :value="modelValue ?? ''"
        :aria-invalid="ariaInvalid"
        class="form-input"
        :maxlength="maxlength"
        :min="min"
        :placeholder="placeholder"
        :step="step"
        :type="type"
        @input="emit('update:modelValue', ($event.target as HTMLInputElement).value)"
    />
</template>
