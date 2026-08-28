<template>
    <div class="small-box gateway-card-box bg-light d-flex p-3">

        <!-- Main content -->
        <div class="flex-grow-1 gateway-content">
            <div class="provider-header">
                <div class="provider-icon-wrap">
                    <i :class="iconClass" class="gateway-icon text-secondary"></i>
                </div>
                <div class="provider-text-wrap">
                    <b class="text-header">{{ label }}</b>
                    <div class="mt-1">
                        <span class="provider-status-label" :class="integration.is_active ? 'text-success' : 'text-danger'">
                            {{ integration.is_active ? __('message.active') : __('message.inactive') }}
                        </span>
                    </div>
                </div>
            </div>

            <p class="text-muted fs-7 mt-2 mb-0">{{ integration.description }}</p>
        </div>

        <!-- Actions -->
        <div class="d-flex align-items-center gap-2 ms-2 flex-shrink-0 align-self-start pt-1">
            <RouterLink
                v-if="integration.is_active"
                :to="`/settings/api/zoho/${integration.platform}`"
                class="btn btn-link p-0 text-muted"
                :title="__('message.settings')"
            >
                <i class="fas fa-gear"></i>
            </RouterLink>

            <Switch
                name="status"
                :value="integration.is_active"
                :disabled="toggling"
                :title="integration.is_active ? __('message.disable') : __('message.enable')"
                :onChange="() => $emit('toggle', integration)"
                classname="gateway-toggle"
            />
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { RouterLink } from 'vue-router'
import Switch from '@/components/Reusable/FormField/Switch.vue'

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

/* Match the old toggle icon's footprint (fa-toggle-on/off at fs-5, ~20px) —
   scoped here so it doesn't shrink the Switch used elsewhere (e.g. auto-renewal). */
:deep(.gateway-toggle .toggle) {
    width: 28px !important;
    height: 12px !important;
}

:deep(.gateway-toggle .toggle-handle) {
    width: 12px !important;
    height: 12px !important;
}
</style>
