<script setup lang="ts">
import { RouterLink } from 'vue-router';
</script>

<template>
    <section class="grid gap-5">
        <h1 class="text-4xl font-semibold tracking-tight text-slate-950">生命保险 Demo</h1>
        <p class="text-lg leading-8 text-slate-700">管理保单列表，创建、查看、编辑和删除保单。</p>
        <RouterLink class="button-primary w-fit" to="/policies">进入保单管理</RouterLink>
    </section>
</template>
