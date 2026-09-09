<template>
    <div class="d-flex gap-1">
        <button
            class="btn btn-light table_btn"
            :disabled="toggling || isDefault"
            v-tooltip="isDefault ? __('message.cannot_disable_default_language') : (status ? __('message.disable') : __('message.enable'))"
            @click="emit('toggle')"
        >
            <i :class="status ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger'"></i>
        </button>
        <button
            v-if="!isDefault && status"
            class="btn btn-light table_btn"
            :disabled="settingDefault"
            v-tooltip="__('message.make_default')"
            @click="emit('set-default')"
        >
            <i class="fas fa-star"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({
    status:        { type: Number,  required: true },
    isDefault:     { type: Boolean, required: true },
    toggling:      { type: Boolean, default: false },
    settingDefault:{ type: Boolean, default: false },
})

const emit = defineEmits(['toggle', 'set-default'])
</script>
