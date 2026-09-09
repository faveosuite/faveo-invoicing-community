<template>
    <div class="form-actions-group">
        <action-button
            :action="action"
            :loading="loading"
            :disabled="disabled"
            @click="$emit('submit')"
        />
        <action-button
            v-if="cancelTo || onCancel"
            action="cancel"
            :to="cancelTo || undefined"
            class="ms-2"
            @click="onCancel ? onCancel() : undefined"
        />
    </div>
</template>

<script setup>
defineProps({
    action:   { type: String,            default: 'save' },
    loading:  { type: Boolean,           default: false },
    disabled: { type: Boolean,           default: false },
    cancelTo: { type: [String, Object],  default: null },
    onCancel: { type: Function,          default: null },
})

defineEmits(['submit'])
</script>

<style scoped>
.form-actions-group {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 0;
}
</style>
