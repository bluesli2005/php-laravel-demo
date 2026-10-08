<script setup lang="ts">
withDefaults(defineProps<{
    modelValue: string | null;
    id: string;
  maxlength?: number | string;
    ariaInvalid?: boolean;
}>(), {
    maxlength: undefined,
    ariaInvalid: false,
});

const emit = defineEmits<{
    'update:modelValue': [value: string];
}>();
</script>

<template>
    <textarea
        :id="id"
        :value="modelValue ?? ''"
        :aria-invalid="ariaInvalid"
        class="form-input min-h-28"
        :maxlength="maxlength"
        @input="emit('update:modelValue', ($event.target as HTMLTextAreaElement).value)"
    ></textarea>
</template>
