<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.zoho_integration') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <div v-else class="card-body">
                <div v-if="!integrations.length" class="text-center text-muted py-4">
                    {{ __('message.no_record_found') }}
                </div>

                <div class="row">
                    <div v-for="item in integrations" :key="item.id" class="col-md-4 mb-4">
                        <ZohoCard
                            :integration="item"
                            :icon-class="platformIcon(item.platform)"
                            :toggling="togglingId === item.id"
                            @toggle="handleToggle"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useAlertStore } from '@/core/stores/alert.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import ZohoCard from './ZohoCard.vue'

const COMPONENT = 'zoho-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route   = useRoute()

const PLATFORM_ICONS = {
    crm:       'fas fa-address-book',
    campaigns: 'fas fa-bullhorn',
}

function platformIcon(platform) {
    return PLATFORM_ICONS[platform] ?? 'fas fa-plug'
}

const loading      = ref(true)
const integrations = ref([])
const togglingId   = ref(null)

async function loadIntegrations(silent = false) {
    if (!silent) loading.value = true
    try {
        const res = await http.get(`${baseUrl}/zoho/integrations`)
        integrations.value = res.data?.data ?? []
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        if (!silent) loading.value = false
    }
}

onMounted(async () => {
    if (route.query.zoho_status) {
        useAlertStore().setAlert({
            type:           route.query.zoho_status === 'success' ? 'success' : 'danger',
            message:        route.query.message ?? '',
            component_name: COMPONENT,
        })
    }
    await loadIntegrations()
})

async function handleToggle(item) {
    if (togglingId.value) return
    togglingId.value = item.id
    try {
        const res = await http.patch(`${baseUrl}/zoho/integrations/${item.id}/toggle`, { is_active: !item.is_active })
        successHandler(res, COMPONENT)
        await loadIntegrations(true)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        togglingId.value = null
    }
}
</script>
