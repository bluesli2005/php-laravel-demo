<script setup lang="ts">
defineProps<{
    message?: string;
}>();
</script>

<template>
    <span v-if="message" class="font-normal text-red-700" role="alert">
        {{ message }}
    </span>
</template>
