<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ plugin?.name ?? pluginSlug }} Settings</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else-if="!plugin">
                <div class="card-body">
                    <div class="alert alert-warning">{{ __('message.payment_gateway_not_found') }}</div>
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
                                    :onChange="(val, name) => { setFieldError(name, undefined); form[name] = val }"
                                    :error="errors[field.name]"
                                />
                            </div>

                            <!-- Webhook URL — read-only, at the end -->
                            <div v-if="form.webhook_url" class="col-md-6 mb-3">
                                <label class="form-label">{{ __('message.webhook_url') }}</label>
                                <div class="input-group">
                                    <input class="form-control" readonly :value="form.webhook_url" />
                                    <button class="btn btn-outline-secondary" type="button" @click="copyWebhookUrl">
                                        <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                    </button>
                                </div>
                                <div class="form-text">{{ __('message.webhook_url_hint') }}</div>
                            </div>
                        </div>

                        <!-- Auto-renewal toggle -->
                        <div class="border-top pt-3 mt-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ __('message.auto_renewal') }}</div>
                                    <div class="text-muted small">{{ __('message.auto_renewal_hint') }}</div>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox"
                                           :checked="form.auto_renewal"
                                           @change="form.auto_renewal = $event.target.checked" />
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Generic info for gateways without a custom form -->
                    <template v-else>
                        <div v-if="plugin.settings" class="alert alert-info">
                            <i class="fas fa-circle-info me-2"></i>
                            {{ __('message.configure_gateway') }}
                            <a :href="`${baseUrl}/${plugin.settings}`" class="ms-2 fw-bold">
                                {{ plugin.settings }} <i class="fas fa-arrow-up-right-from-square ms-1"></i>
                            </a>
                        </div>
                        <div v-else class="text-muted">{{ __('message.no_additional_settings') }}</div>
                    </template>
                </div>

                <div v-if="gatewayConfig" class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { __ } from '@/plugins/i18n'
import { buildGatewaySchema } from '@/validations/admin/gatewayValidations'

const COMPONENT = 'payment-gateway-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const pluginSlug = route.params.id

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving  = ref(false)
const copied  = ref(false)
const plugin  = ref(null)
const form    = reactive({})

// Gateway-specific config: fields to show + how to fetch/save
const GATEWAY_CONFIGS = {
    razorpay: {
        fields: [
            { name: 'rzp_key',        label: 'Razorpay Key',                                      type: 'text'     },
            { name: 'rzp_secret',     label: 'Razorpay Secret',                                   type: 'password' },
            { name: 'webhook_secret', label: 'Webhook Secret',                                    type: 'password', required: false },
            { name: 'apilayer_key',   label: 'ApiLayer Access Key (For Exchange Rate Conversion)', type: 'text',     required: false },
            { name: 'processing_fee', label: 'Processing Fee (%)',                                type: 'number',   required: false },
        ],
        fetchUrl: `${baseUrl}/get-razorpay-settings`,
        saveUrl:  `${baseUrl}/update-api-key/payment-gateway/razorpay`,
    },
    stripe: {
        fields: [
            { name: 'stripe_key',     label: 'Stripe Publishable Key', type: 'text'     },
            { name: 'stripe_secret',  label: 'Stripe Secret Key',      type: 'password' },
            { name: 'webhook_secret', label: 'Webhook Secret',         type: 'password', required: false },
            { name: 'processing_fee', label: 'Processing Fee (%)',     type: 'number',   required: false },
        ],
        fetchUrl: `${baseUrl}/get-stripe-settings`,
        saveUrl:  `${baseUrl}/update-api-key/payment-gateway/stripe`,
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
            form.webhook_url   = data.webhook_url ?? ''
            form.auto_renewal  = data.auto_renewal ?? false
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

function copyWebhookUrl() {
    navigator.clipboard.writeText(form.webhook_url)
    copied.value = true
    setTimeout(() => { copied.value = false }, 2000)
}

async function save() {
    if (!gatewayConfig.value) return
    try {
        buildGatewaySchema(gatewayConfig.value.fields).validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
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
