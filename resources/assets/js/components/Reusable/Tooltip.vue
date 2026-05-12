<template>
    <span style="margin-left: 2px;position:relative;top:0px;color: #337ab7;" v-tooltip="message" :title="message">
        <i class="fas fa-question-circle" :style="{fontSize: size}" style="cursor: help"></i>
    </span>
</template>

<script setup>
defineProps({
    message: { type: String, required: true },
    size: { type: String, default: 'medium' },
})
</script>

