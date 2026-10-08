<script setup lang="ts">
withDefaults(defineProps<{
    type?: 'button' | 'submit' | 'reset';
    variant?: 'primary' | 'secondary';
    disabled?: boolean;
}>(), {
    type: 'button',
    variant: 'primary',
    disabled: false,
});
</script>

<template>
    <button :class="variant === 'primary' ? 'button-primary' : 'button-secondary'" :disabled="disabled" :type="type">
        <slot />
    </button>
</template>
