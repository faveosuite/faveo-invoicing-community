<template>
    <div class="d-flex gap-1">
        <button
            class="btn btn-light table_btn"
            :title="isDefault ? 'Cannot disable the default currency' : isDashboard ? 'Cannot disable a dashboard currency' : (status ? 'Disable' : 'Enable')"
            :disabled="toggling || isDefault || isDashboard"
            v-tooltip
            @click="emit('toggle')"
        >
            <span v-if="toggling" class="spinner-border spinner-border-sm"></span>
            <i v-else :class="status ? 'fas fa-toggle-on text-success' : 'fas fa-toggle-off text-danger'"></i>
        </button>
        <button
            v-if="!isDefault && status"
            class="btn btn-light table_btn"
            title="Set as Default Currency"
            :disabled="settingDefault"
            v-tooltip
            @click="emit('set-default')"
        >
            <span v-if="settingDefault" class="spinner-border spinner-border-sm"></span>
            <i v-else class="fas fa-star"></i>
        </button>
        <button
            v-if="status && !isDashboard"
            class="btn btn-light table_btn"
            title="Set as Dashboard Currency"
            :disabled="settingDashboard"
            v-tooltip
            @click="emit('set-dashboard')"
        >
            <span v-if="settingDashboard" class="spinner-border spinner-border-sm"></span>
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
