<script setup lang="ts">
export interface TableColumn {
    key: string;
    label: string;
}

defineProps<{
    columns: readonly TableColumn[];
    rows: readonly object[];
    rowKey: string;
}>();

function getCellValue(row: object, key: string): unknown {
    return (row as Record<string, unknown>)[key];
}

function displayValue(value: unknown): string {
    return value === null || value === undefined ? '' : String(value);
}
</script>

<template>
    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-slate-600">
                <tr>
                    <th v-for="column in columns" :key="column.key" class="px-4 py-3">
                        {{ column.label }}
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <tr v-for="row in rows" :key="displayValue(getCellValue(row, rowKey))">
                    <td v-for="column in columns" :key="column.key" class="px-4 py-3">
                        <slot
                            :name="`cell-${column.key}`"
                            :row="row"
                            :value="getCellValue(row, column.key)"
                        >
                            {{ displayValue(getCellValue(row, column.key)) }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
