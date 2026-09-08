<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.payment_gateways') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div v-if="!plugins.length" class="text-center text-muted py-4">
                        {{ __('message.no_payment_gateways') }}
                    </div>

                    <div class="row">
                        <div v-for="plugin in plugins" :key="plugin.name" class="col-md-4 mb-4">
                            <GatewayCard
                                :plugin="plugin"
                                :logo-src="gatewayLogo(plugin.name)"
                                :icon-class="gatewayIcon(plugin.name)"
                                :toggling="toggling === plugin.name"
                                :description="gatewayDescription(plugin.name) || plugin.description || __('message.no_description_available')"
                                @toggle="toggleStatus"
                                @settings="goToSettings"
                            />
                        </div>
                    </div>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import GatewayCard from './GatewayCard.vue'

const COMPONENT = 'payment-gateway-index'

const router = useRouter()
const loading = ref(true)
const toggling = ref(null)
const plugins = ref([])

const GATEWAY_LOGOS = {
}

const GATEWAY_DESCRIPTIONS = {
    paypal:       __('message.gateway_desc_paypal'),
    stripe:       __('message.gateway_desc_stripe'),
    razorpay:     __('message.gateway_desc_razorpay'),
    amazon:       __('message.gateway_desc_amazon'),
    google:       __('message.gateway_desc_google'),
    apple:        __('message.gateway_desc_apple'),
    mollie:       __('message.gateway_desc_mollie'),
    paytm:        __('message.gateway_desc_paytm'),
    cashfree:     __('message.gateway_desc_cashfree'),
    instamojo:    __('message.gateway_desc_instamojo'),
    flutterwave:  __('message.gateway_desc_flutterwave'),
    paystack:     __('message.gateway_desc_paystack'),
    square:       __('message.gateway_desc_square'),
    braintree:    __('message.gateway_desc_braintree'),
    authorize:    __('message.gateway_desc_authorize'),
    ccavenue:     __('message.gateway_desc_ccavenue'),
    payu:         __('message.gateway_desc_payu'),
    worldpay:     __('message.gateway_desc_worldpay'),
    klarna:       __('message.gateway_desc_klarna'),
    afterpay:     __('message.gateway_desc_afterpay'),
}

const GATEWAY_ICONS = {
    paypal:       'fab fa-paypal',
    stripe:       'fab fa-stripe-s',
    amazon:       'fab fa-amazon-pay',
    google:       'fab fa-google-pay',
    apple:        'fab fa-apple-pay',
    razorpay:     'fas fa-bolt',
    paytm:        'fas fa-wallet',
    mollie:       'fas fa-credit-card',
    cashfree:     'fas fa-money-bill-wave',
    instamojo:    'fas fa-bolt',
    flutterwave:  'fas fa-wave-square',
    paystack:     'fas fa-building-columns',
    square:       'fab fa-square',
    braintree:    'fas fa-brain',
    authorize:    'fas fa-shield-halved',
    ccavenue:     'fas fa-credit-card',
    payu:         'fas fa-coins',
    twocheckout:  'fas fa-2',
    worldpay:     'fas fa-globe',
    klarna:       'fas fa-k',
    afterpay:     'fas fa-calendar-check',
}

function gatewayDescription(name) {
    const key = name.toLowerCase().replace(/\s+/g, '')
    const match = Object.entries(GATEWAY_DESCRIPTIONS).find(([k]) => key.includes(k))
    return match ? match[1] : null
}

function gatewayIcon(name) {
    const key = name.toLowerCase().replace(/\s+/g, '')
    const match = Object.entries(GATEWAY_ICONS).find(([k]) => key.includes(k))
    return match ? match[1] : 'fas fa-credit-card'
}

function gatewayLogo(name) {
    const key = name.toLowerCase().replace(/\s+/g, '')
    const match = Object.entries(GATEWAY_LOGOS).find(([k]) => key.includes(k))
    return match ? match[1] : null
}

function goToSettings(plugin) {
    router.push(`/settings/payment-gateway/${plugin.name}/edit`)
}

onMounted(loadPlugins)

async function loadPlugins() {
    loading.value = true
    try {
        const res = await http.get(`/payment-gateway-list`)
        plugins.value = res.data?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
}

async function toggleStatus(plugin) {
    toggling.value = plugin.name
    try {
        const res = await http.post(`/updatePaymentStatus`, {
            name:   plugin.name,
            status: plugin.status ? 0 : 1,
        })
        successHandler(res, COMPONENT)
        await loadPlugins()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { toggling.value = null }
}
</script>

