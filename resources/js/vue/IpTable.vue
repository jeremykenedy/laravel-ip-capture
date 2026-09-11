<script setup>
import { computed } from 'vue'

const props = defineProps({
    rows: { type: Array, default: () => [] },
    title: { type: String, default: 'Captured IP addresses' },
    theme: { type: String, default: 'tailwind' },
    eventHeader: { type: String, default: 'Event' },
    valueHeader: { type: String, default: 'Address' },
    emptyText: { type: String, default: 'No IP addresses have been captured yet.' },
})

const themes = {
    tailwind: {
        card: 'overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-900',
        header: 'border-b border-gray-200 px-4 py-3 dark:border-gray-700',
        title: 'text-sm font-semibold text-gray-900 dark:text-gray-100',
        table: 'min-w-full divide-y divide-gray-200 text-left text-sm dark:divide-gray-700',
        th: 'px-4 py-2 text-xs font-medium uppercase tracking-wide text-gray-500 dark:text-gray-400',
        label: 'whitespace-nowrap px-4 py-2 font-normal text-gray-600 dark:text-gray-300',
        cell: 'px-4 py-2',
        code: 'rounded bg-gray-100 px-1.5 py-0.5 font-mono text-xs text-gray-900 dark:bg-gray-800 dark:text-gray-100',
        empty: 'text-gray-400 dark:text-gray-500',
    },
    bootstrap5: {
        card: 'card',
        header: 'card-header',
        title: 'h6 mb-0',
        table: 'table table-sm mb-0 align-middle',
        th: 'text-secondary text-uppercase small',
        label: 'fw-normal text-body-secondary',
        cell: '',
        code: 'font-monospace',
        empty: 'text-secondary',
    },
    bootstrap4: {
        card: 'card',
        header: 'card-header',
        title: 'h6 mb-0',
        table: 'table table-sm mb-0',
        th: 'text-muted text-uppercase small',
        label: 'font-weight-normal text-muted',
        cell: '',
        code: '',
        empty: 'text-muted',
    },
}

const css = computed(() => themes[props.theme] ?? themes.tailwind)
</script>

<template>
    <div :class="css.card">
        <div :class="css.header">
            <h3 :class="css.title">{{ title }}</h3>
        </div>

        <div class="table-responsive">
            <table :class="css.table">
                <thead>
                    <tr>
                        <th scope="col" :class="css.th">{{ eventHeader }}</th>
                        <th scope="col" :class="css.th">{{ valueHeader }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="row in rows" :key="row.column" :data-column="row.column">
                        <th scope="row" :class="css.label">{{ row.label }}</th>
                        <td :class="css.cell">
                            <code v-if="row.captured" :class="css.code">{{ row.value }}</code>
                            <span v-else :class="css.empty">{{ row.value }}</span>
                        </td>
                    </tr>
                    <tr v-if="rows.length === 0">
                        <td colspan="2" :class="css.empty">{{ emptyText }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
