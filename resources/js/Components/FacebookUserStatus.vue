<script setup>
defineProps({
    status: [Number, String],
})

const PENDING = 0
const PROCESSING = 1
const SUCCESS = 2
</script>

<template>
    <span
        v-if="status == PENDING"
        class="me-2 rounded-sm bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900 dark:text-blue-300"
    >
        Pending
    </span>
    <span
        v-else-if="status == PROCESSING"
        class="me-2 rounded-sm bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300"
    >
        In process
    </span>
    <span
        v-else-if="status == SUCCESS"
        class="me-2 rounded-sm bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900 dark:text-green-300"
    >
        Success
    </span>
</template>
