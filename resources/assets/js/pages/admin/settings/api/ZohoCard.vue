<template>
    <div class="small-box gateway-card-box bg-light d-flex flex-column p-3">

        <!-- Header row: icon + name/status | toggle + settings -->
        <div class="d-flex align-items-start justify-content-between">
            <div class="provider-header">
                <div class="provider-icon-wrap">
                    <i :class="iconClass" class="gateway-icon text-secondary"></i>
                </div>
                <div class="provider-text-wrap">
                    <div class="d-flex align-items-center gap-2">
                        <b class="text-header">{{ label }}</b>
                        <RouterLink
                            v-if="integration.is_active"
                            :to="`/settings/api/zoho/${integration.platform}`"
                            class="text-muted settings-icon"
                            v-tooltip :title="__('message.settings')"
                        >
                            <i class="fas fa-cog"></i>
                        </RouterLink>
                    </div>
                    <div class="mt-1">
                        <span class="provider-status-label" :class="integration.is_active ? 'text-success' : 'text-secondary'">
                            {{ integration.is_active ? __('message.enabled') : __('message.disabled') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="form-check form-switch mb-0 flex-shrink-0">
                <input
                    class="form-check-input"
                    type="checkbox"
                    role="switch"
                    :checked="integration.is_active"
                    :disabled="toggling"
                    style="cursor:pointer"
                    @change="$emit('toggle', integration)"
                />
            </div>
        </div>

        <!-- Description -->
        <p class="text-muted fs-7 mt-3 mb-0">{{ integration.description }}</p>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({
    integration: { type: Object, required: true },
    iconClass:   { type: String, default: 'fas fa-plug' },
    toggling:    { type: Boolean, default: false },
})

defineEmits(['toggle'])

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

.settings-icon {
    font-size: 15px;
    opacity: 0.65;
    transition: opacity 0.15s;
    text-decoration: none;
}

.settings-icon:hover {
    opacity: 1;
}
</style>
