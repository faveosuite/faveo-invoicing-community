<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title mb-0">{{ plugin?.name ?? pluginSlug }} Settings</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

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
                                    :hint="field.hint ?? ''"
                                />
                            </div>

                            <!-- Webhook URL — read-only, shown before the secret: the admin
                                 copies this first to create the webhook on the gateway's
                                 dashboard, which is what then gives them the secret below. -->
                            <div v-if="form.webhook_url" class="col-md-6 mb-3">
                                <label class="form-label">
                                    {{ __('message.webhook_url') }}
                                    <Tooltip v-if="gatewayConfig.webhookUrlHint" :message="gatewayConfig.webhookUrlHint" size="small" />
                                </label>
                                <div class="input-group">
                                    <input class="form-control" readonly :value="form.webhook_url" />
                                    <button class="btn btn-outline-secondary" type="button" @click="copyWebhookUrl">
                                        <i :class="copied ? 'fas fa-check text-success' : 'fas fa-copy'"></i>
                                    </button>
                                </div>
                                <div class="form-text">
                                    {{ __('message.webhook_url_hint') }}
                                    <a v-if="gatewayConfig.webhookEvents?.length" href="#" @click.prevent="showWebhookEventsModal = true">
                                        {{ __('message.view_required_webhook_events') }}
                                    </a>
                                </div>
                            </div>

                            <!-- Webhook Secret — filled in after creating the webhook above -->
                            <div class="col-md-6 mb-3">
                                <TextField
                                    name="webhook_secret"
                                    label="Webhook Secret"
                                    type="password"
                                    :value="form.webhook_secret"
                                    :onChange="(val, name) => { setFieldError(name, undefined); form[name] = val }"
                                    :error="errors.webhook_secret"
                                />
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

        <!-- Required webhook events — reference doc: what to select in the
             gateway's dashboard and why, not something meant to be copied. -->
        <Modal :showModal="showWebhookEventsModal" :onClose="() => showWebhookEventsModal = false" :showCloseBtn="true" classname="modal-lg">
            <template #title>
                <h5 class="modal-title">{{ __('message.required_webhook_events') }}</h5>
            </template>
            <template #fields>
                <p class="text-muted">{{ __('message.required_webhook_events_hint') }}</p>
                <ul class="list-group">
                    <li v-for="event in gatewayConfig?.webhookEvents ?? []" :key="event.name" class="list-group-item">
                        <code>{{ event.name }}</code>
                        <div class="text-muted small mt-1">{{ event.purpose }}</div>
                    </li>
                </ul>
            </template>
        </Modal>

        <!-- Confirm before a save that would disable auto-renewal for this
             gateway — that save cancels every currently-enrolled customer's
             auto-renewal immediately, not just new opt-ins going forward. -->
        <Modal :showModal="showDisableWarningModal" :onClose="() => showDisableWarningModal = false" :showCloseBtn="false">
            <template #title>
                <h5 class="modal-title text-danger">{{ __('message.disable_gateway_auto_renewal_title') }}</h5>
            </template>
            <template #fields>
                <p class="mb-0">{{ __('message.disable_gateway_auto_renewal_warning') }}</p>
            </template>
            <template #controls>
                <button type="button" class="btn btn-light me-2" @click="showDisableWarningModal = false">
                    {{ __('message.cancel') }}
                </button>
                <button type="button" class="btn btn-danger" :disabled="saving" @click="confirmDisableAndSave">
                    <i v-if="saving" class="fas fa-circle-notch fa-spin me-1"></i>
                    {{ __('message.disable') }}
                </button>
            </template>
        </Modal>
    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { __ } from '@/plugins/i18n'
import { buildGatewaySchema } from '@/validations/admin/gatewayValidations'
import { useBaseUrl } from '@/core/composables/useBaseUrl'
import Tooltip from '@/components/Reusable/Tooltip.vue'
import Modal from '@/themes/porto/components/common/Modal.vue'

const COMPONENT = 'payment-gateway-edit'
const baseUrl = useBaseUrl()
const route = useRoute()
const pluginSlug = route.params.id

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving  = ref(false)
const copied  = ref(false)
const plugin  = ref(null)
const form    = reactive({})

const showWebhookEventsModal  = ref(false)
const showDisableWarningModal = ref(false)
// The last-saved value — compared against form.auto_renewal to detect an
// on-to-off transition specifically, not just "the switch is currently off"
// (which would also fire the warning on every save while it's already off).
const savedAutoRenewal = ref(false)

// Gateway-specific config: fields to show + how to fetch/save
const GATEWAY_CONFIGS = {
    razorpay: {
        fields: [
            { name: 'rzp_key',        label: 'Razorpay Key',        type: 'text'     },
            { name: 'rzp_secret',     label: 'Razorpay Secret',     type: 'password' },
            { name: 'processing_fee', label: 'Processing Fee (%)', type: 'number',   required: false },
        ],
        fetchUrl: `${baseUrl}/get-razorpay-settings`,
        saveUrl:  `${baseUrl}/update-api-key/payment-gateway/razorpay`,
        webhookEvents: [
            { name: 'subscription.charged', purpose: 'Required for auto-renewal to actually work — without it, renewed subscriptions won\'t be extended even after Razorpay charges the customer.' },
            { name: 'subscription.halted', purpose: 'Required to detect failed renewals — without it, the app won\'t know a recurring charge stopped working, and auto-renewal stays silently broken.' },
            { name: 'payment.captured', purpose: 'Required to confirm invoice/order payments — without it, a customer can pay successfully and the invoice still won\'t be marked as paid.' },
            { name: 'payment.failed', purpose: 'Required to detect failed payments — without it, a failed payment attempt goes unnoticed and the customer is never told to retry.' },
        ],
    },
    stripe: {
        fields: [
            { name: 'stripe_key',     label: 'Stripe Publishable Key', type: 'text'     },
            { name: 'stripe_secret',  label: 'Stripe Secret Key',      type: 'password' },
            { name: 'processing_fee', label: 'Processing Fee (%)',     type: 'number',   required: false },
        ],
        fetchUrl: `${baseUrl}/get-stripe-settings`,
        saveUrl:  `${baseUrl}/update-api-key/payment-gateway/stripe`,
        webhookUrlHint: __('message.stripe_webhook_url_hint'),
        webhookEvents: [
            { name: 'invoice.payment_succeeded', purpose: 'Required for auto-renewal to actually work — without it, renewed subscriptions won\'t be extended even after Stripe charges the customer.' },
            { name: 'invoice.payment_failed', purpose: 'Required to detect failed renewals — without it, the app won\'t know a recurring charge stopped working, and auto-renewal stays silently broken.' },
            { name: 'customer.subscription.deleted', purpose: 'Required to keep auto-renewal status accurate — without it, a subscription cancelled in Stripe still shows as active here.' },
            { name: 'checkout.session.completed', purpose: 'Required to confirm invoice/order payments made via embedded Checkout — without it, a customer can pay successfully and the invoice still won\'t be marked as paid.' },
            { name: 'payment_intent.succeeded', purpose: 'Required to confirm invoice/order payments made by card — without it, a customer can pay successfully and the invoice still won\'t be marked as paid.' },
            { name: 'payment_intent.payment_failed', purpose: 'Required to detect failed payments — without it, a failed payment attempt goes unnoticed and the customer is never told to retry.' },
        ],
    },
}

const gatewayConfig = computed(() => {
    const key = pluginSlug?.toLowerCase()
    return Object.entries(GATEWAY_CONFIGS).find(([k]) => key?.includes(k))?.[1] ?? null
})

onMounted(async () => {
    try {
        const listRes = await http.get(`/payment-gateway-list`)
        const list    = listRes.data?.data ?? []
        plugin.value  = list.find(p => p.name === pluginSlug) ?? null

        if (gatewayConfig.value) {
            const settingsRes = await http.get(gatewayConfig.value.fetchUrl)
            const data = settingsRes.data?.data ?? {}
            gatewayConfig.value.fields.forEach(f => { form[f.name] = data[f.name] ?? '' })
            form.webhook_url     = data.webhook_url ?? ''
            form.webhook_secret  = data.webhook_secret ?? ''
            form.auto_renewal    = data.auto_renewal ?? false
            savedAutoRenewal.value = form.auto_renewal
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
    if (!await validateForm(buildGatewaySchema(gatewayConfig.value.fields), form, setErrors)) return

    // Only warn on the on-to-off transition — saving while it's already off,
    // or while it's staying on, cancels nothing.
    if (savedAutoRenewal.value && !form.auto_renewal) {
        showDisableWarningModal.value = true
        return
    }

    await performSave()
}

async function confirmDisableAndSave() {
    showDisableWarningModal.value = false
    await performSave()
}

async function performSave() {
    saving.value = true
    try {
        const payload = { ...form, auto_renewal: form.auto_renewal ? 1 : 0 }
        const res = await http.post(gatewayConfig.value.saveUrl, payload)
        successHandler(res, COMPONENT)
        savedAutoRenewal.value = form.auto_renewal
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
