<template>
    <div class="small-box gateway-card-box bg-light d-flex flex-column p-3">

        <!-- Main content -->
        <div class="d-flex">
            <div class="flex-grow-1 gateway-content">
                <div class="provider-header">
                    <div class="provider-icon-wrap">
                        <i :class="iconClass" class="gateway-icon text-secondary"></i>
                    </div>

                    <div class="provider-text-wrap">
                        <b class="text-header">{{ label }}</b>
                        <div class="mt-1">
                            <span class="provider-status-label" :class="integration.is_active ? 'text-success' : 'text-danger'">
                                {{ integration.is_active ? __('message.connected') : __('message.not_connected') }}
                            </span>
                        </div>
                    </div>
                </div>

                <p class="text-muted fs-7 mt-2 mb-0">{{ integration.description }}</p>
            </div>

            <!-- Field mapping shortcut (only once connected) -->
            <div class="d-flex align-items-center gap-2 ms-2 flex-shrink-0 align-self-start pt-1">
                <button
                    v-if="integration.is_active"
                    class="btn btn-link p-0 text-muted"
                    :title="__('message.field_mapping')"
                    @click="$emit('mapping', integration)"
                >
                    <i class="fas fa-sliders"></i>
                </button>
            </div>
        </div>

        <!-- Primary action -->
        <div class="mt-auto pt-3 text-end">
            <button
                type="button"
                class="btn btn-sm"
                :class="integration.is_active ? 'btn-outline-secondary' : 'btn-primary'"
                @click="$emit('connect', integration)"
            >
                <i class="fas fa-plug me-1"></i>
                {{ integration.is_active ? __('message.reconnect') : __('message.connect') }}
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
    integration: { type: Object, required: true },
    iconClass:   { type: String, default: 'fas fa-plug' },
})

defineEmits(['connect', 'mapping'])

const label = computed(() => {
    const map = { crm: __('message.zoho_crm'), campaigns: __('message.zoho_campaigns') }
    return map[props.integration.platform] ?? `Zoho ${props.integration.platform}`
})
</script>

<style scoped>
.gateway-card-box {
    min-height: 165px;
}

.gateway-content {
    min-width: 0;
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
