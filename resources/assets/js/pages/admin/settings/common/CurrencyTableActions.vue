<template>
    <div class="d-flex gap-1">
        <button
            class="btn btn-light table_btn"
            :disabled="toggling || isDefault || isDashboard"
            v-tooltip="isDefault ? __('message.cannot_disable_default_currency') : isDashboard ? __('message.cannot_disable_dashboard_currency') : (status ? __('message.disable') : __('message.enable'))"
            @click="emit('toggle')"
        >
            <spinner-loader v-if="toggling" :size="18" />
            <i v-else :class="status ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger'"></i>
        </button>
        <button
            v-if="!isDefault && status"
            class="btn btn-light table_btn"
            :disabled="settingDefault"
            v-tooltip="__('message.set_as_default_currency')"
            @click="emit('set-default')"
        >
            <spinner-loader v-if="settingDefault" :size="18" />
            <i v-else class="fas fa-star"></i>
        </button>
        <button
            v-if="status && !isDashboard"
            class="btn btn-light table_btn"
            :disabled="settingDashboard"
            v-tooltip="__('message.set_as_dashboard_currency')"
            @click="emit('set-dashboard')"
        >
            <spinner-loader v-if="settingDashboard" :size="18" />
            <i v-else class="fas fa-chart-bar"></i>
        </button>
    </div>
</template>

<script setup>
defineProps({
    status:          { type: Number,  required: true },
    isDefault:       { type: Boolean, default: false },
    isDashboard:     { type: Boolean, default: false },
    toggling:        { type: Boolean, default: false },
    settingDefault:  { type: Boolean, default: false },
    settingDashboard:{ type: Boolean, default: false },
})

const emit = defineEmits(['toggle', 'set-default', 'set-dashboard'])
</script>
