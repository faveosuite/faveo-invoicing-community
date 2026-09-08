<template>
    <div v-if="actions.length" class="d-flex gap-1">
        <button
            v-for="action in actions"
            :key="action.key ?? action.label"
            type="button"
            class="btn btn-light border"
            :disabled="action.disabled"
            @click="action.onClick"
        >
            <spinner-loader v-if="action.loading" :size="16" />
            <i v-else :class="action.icon"></i>
            {{ action.label }}
        </button>
    </div>
</template>

<script setup>
// Icon+label bulk-action row, shown once >=1 row is checked — replaces the old
// "Bulk Action" dropdown-toggle button. Each entry in `actions` is a
// { key?, icon, label, onClick, disabled?, loading? } object; the caller's
// onClick is expected to open its own confirm modal (matching DeleteModal
// etc.), not run the action directly.
defineProps({
    actions: { type: Array, default: () => [] },
})
</script>
