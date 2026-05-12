<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h4 class="card-title mb-0">{{ plugin?.name ?? pluginSlug }} Settings</h4>
                <RouterLink to="/settings/payment-gateway" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back
                </RouterLink>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else-if="!plugin">
                <div class="card-body">
                    <div class="alert alert-warning">Payment gateway not found.</div>
                </div>
            </template>

            <template v-else>
                <div class="card-body">
                    <!-- Gateway-specific settings form -->
                    <template v-if="gatewayConfig">
                        <div class="row">
                            <div v-for="field in gatewayConfig.fields" :key="field.name" class="col-md-6 mb-3">
                                <TextField
                                    :name="field.name"
                                    :label="field.label"
                                    :type="field.type ?? 'text'"
                                    :value="form[field.name]"
                                    :onChange="(val, name) => form[name] = val"
                                />
                            </div>
                        </div>
                    </template>

                    <!-- Generic info for gateways without a custom form -->
                    <template v-else>
                        <div v-if="plugin.settings" class="alert alert-info">
                            <i class="fas fa-circle-info me-2"></i>
                            Configure detailed settings for this payment gateway at:
                            <a :href="`${baseUrl}/${plugin.settings}`" class="ms-2 fw-bold">
                                {{ plugin.settings }} <i class="fas fa-arrow-up-right-from-square ms-1"></i>
                            </a>
                        </div>
                        <div v-else class="text-muted">No additional settings available.</div>
                    </template>
                </div>

                <div v-if="gatewayConfig" class="card-footer">
                    <button class="btn btn-primary" :disabled="saving" @click="save">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        Save
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'payment-gateway-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const pluginSlug = route.params.id

const loading = ref(true)
const saving  = ref(false)
const plugin  = ref(null)
const form    = reactive({})

// Gateway-specific config: fields to show + how to fetch/save
const GATEWAY_CONFIGS = {
    razorpay: {
        fields: [
            { name: 'rzp_key',      label: 'Razorpay Key',                                       type: 'text'     },
            { name: 'rzp_secret',   label: 'Razorpay Secret',                                    type: 'password' },
            { name: 'apilayer_key', label: 'ApiLayer Access Key (For Exchange Rate Conversion)',  type: 'text'     },
        ],
        fetchUrl: `${baseUrl}/get-razorpay-settings`,
        saveUrl:  `${baseUrl}/update-api-key/payment-gateway/razorpay`,
    },
}

const gatewayConfig = computed(() => {
    const key = pluginSlug?.toLowerCase()
    return Object.entries(GATEWAY_CONFIGS).find(([k]) => key?.includes(k))?.[1] ?? null
})

onMounted(async () => {
    try {
        const listRes = await http.get(`${baseUrl}/payment-gateway-list`)
        const list    = listRes.data?.data ?? []
        plugin.value  = list.find(p => p.name === pluginSlug) ?? null

        if (gatewayConfig.value) {
            const settingsRes = await http.get(gatewayConfig.value.fetchUrl)
            const data = settingsRes.data?.data ?? {}
            gatewayConfig.value.fields.forEach(f => { form[f.name] = data[f.name] ?? '' })
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    if (!gatewayConfig.value) return
    saving.value = true
    try {
        const res = await http.get(gatewayConfig.value.saveUrl, { params: form })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
