<template>
    <span class="tooltip-wrapper" v-tooltip="message" :title="message">
        <i class="fas fa-question-circle tooltip-icon" :style="{fontSize: size}"></i>
    </span>
</template>

<script setup>
defineProps({
    message: { type: String, required: true },
    size: { type: String, default: 'medium' },
})
</script>

<style scoped>
.tooltip-wrapper { margin-left: 2px; position: relative; top: 0; color: #337ab7; }
.tooltip-icon { cursor: help; }
</style>
