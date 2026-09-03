<template>
    <div :class="`alert alert-${type} d-flex align-items-center`" role="alert">
        <i :class="iconClass" class="me-2 flex-shrink-0"></i>
        <slot />
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    type: { type: String, default: 'info' },
})

const iconClass = computed(() => ({
    'fas fa-circle-check':         props.type === 'success',
    'fas fa-triangle-exclamation': props.type === 'danger',
    'fas fa-circle-exclamation':   props.type === 'warning',
    'fas fa-circle-info':          props.type === 'info',
}))
</script>
