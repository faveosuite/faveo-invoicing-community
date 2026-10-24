<template>
    <ActionButton v-bind="$attrs" light size="sm">
        <template v-for="(_, name) in $slots" #[name]="slotProps">
            <slot :name="name" v-bind="slotProps" />
        </template>
    </ActionButton>
</template>

<script setup>
import ActionButton from '@/components/Reusable/ActionButton.vue'

defineOptions({ inheritAttrs: false })
</script>
