<template>
    <ActionButton v-bind="$attrs" light size="sm">
        <slot />
    </ActionButton>
</template>

<script setup>
import ActionButton from '@/components/Reusable/ActionButton.vue'

defineOptions({ inheritAttrs: false })
</script>
