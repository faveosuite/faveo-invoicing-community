<template>
    <div class="small-box gateway-card-box bg-light d-flex p-3">

        <!-- Main content -->
        <div class="flex-grow-1 gateway-content">
            <div class="provider-header">
                <div class="provider-icon-wrap">
                    <img v-if="logoSrc" :src="logoSrc" :alt="plugin.name" class="gateway-logo" />
                    <i v-else :class="iconClass" class="gateway-icon text-secondary"></i>
                </div>

                <div class="provider-text-wrap">
                    <b class="text-header">{{ plugin.name }}</b>
                    <div class="mt-1">
                        <span class="provider-status-label" :class="plugin.status ? 'text-success' : 'text-danger'">
                            {{ plugin.status ? __('message.active') : __('message.inactive') }}
                        </span>
                    </div>
                </div>
            </div>

            <p class="text-muted fs-7 mt-2 mb-0">{{ description }}</p>
        </div>

        <!-- Actions -->
        <div class="d-flex align-items-center gap-2 ms-2 flex-shrink-0 align-self-start pt-1">
            <button class="btn btn-link p-0 text-muted" :title="__('message.settings')" @click="$emit('settings', plugin)">
                <i class="fas fa-gear"></i>
            </button>

            <button
                class="btn btn-link p-0"
                :class="{ 'opacity-50 pe-none': toggling }"
                :title="plugin.status ? __('message.disable') : __('message.enable')"
                :disabled="toggling"
                @click="$emit('toggle', plugin)"
            >
                <i :class="plugin.status ? 'fa-solid fa-toggle-on' : 'fa-solid fa-toggle-off'" class="text-secondary fs-5"></i>
            </button>
        </div>
    </div>
</template>

<script setup>
defineProps({
    plugin:      { type: Object,  required: true },
    logoSrc:     { type: String,  default: null },
    iconClass:   { type: String,  default: 'fas fa-credit-card' },
    toggling:    { type: Boolean, default: false },
    description: { type: String,  default: 'No description available.' },
})

defineEmits(['toggle', 'settings'])
</script>

<style scoped>
.gateway-card-box {
    min-height: 165px;
}

.gateway-content {
    min-width: 0;
}

.gateway-logo {
    width: 32px;
    height: 32px;
    object-fit: contain;
}

.gateway-icon {
    font-size: 22px;
}

.provider-header {
    display: flex;
    align-items: flex-start;
    gap: 12px;
}

.provider-icon-wrap {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: #eef2f6;
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 0 0 56px;
}

.provider-text-wrap {
    display: flex;
    flex-direction: column;
    justify-content: center;
    min-width: 0;
}

.text-header {
    font-size: 15px;
}

.provider-status-label {
    font-size: 14px;
    font-weight: 700;
    line-height: 1.1;
}
</style>
