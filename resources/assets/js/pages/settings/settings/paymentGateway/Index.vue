<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Payment Gateways</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div v-if="!plugins.length" class="text-center text-muted py-4">
                        No payment gateways found.
                    </div>

                    <div class="row">
                        <div v-for="plugin in plugins" :key="plugin.name" class="col-md-4 mb-4">
                            <GatewayCard
                                :plugin="plugin"
                                :logo-src="gatewayLogo(plugin.name)"
                                :icon-class="gatewayIcon(plugin.name)"
                                :toggling="toggling === plugin.name"
                                :description="gatewayDescription(plugin.name) || plugin.description || 'No description available.'"
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
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const router = useRouter()
const loading = ref(true)
const toggling = ref(null)
const plugins = ref([])

const GATEWAY_LOGOS = {
}

const GATEWAY_DESCRIPTIONS = {
    paypal:       'Accept payments worldwide using PayPal — one of the most trusted online payment platforms supporting 200+ countries and multiple currencies.',
    stripe:       'Process cards, wallets, and local payment methods globally with Stripe\'s powerful developer-friendly payment infrastructure.',
    razorpay:     'India\'s leading payment gateway supporting UPI, cards, net banking, wallets, and EMI with seamless checkout experience.',
    amazon:       'Let customers pay using their Amazon account credentials for a fast, trusted, and familiar checkout experience.',
    google:       'Enable fast and secure checkout with Google Pay — works across Android, Chrome, and the web.',
    apple:        'Allow Apple device users to pay quickly and securely using Face ID, Touch ID, or passcode via Apple Pay.',
    mollie:       'A flexible European payment gateway offering cards, iDEAL, Bancontact, SEPA, and many more local methods.',
    paytm:        'India\'s popular digital wallet and payment gateway supporting UPI, cards, net banking, and Paytm wallet.',
    cashfree:     'Fast and reliable payment gateway for Indian businesses with support for payouts, subscriptions, and instant settlements.',
    instamojo:    'Simple payment gateway for Indian SMBs — accept payments via links, UPI, cards, and wallets with zero setup fees.',
    flutterwave:  'Pan-African payment platform enabling businesses to accept and send payments across Africa and beyond.',
    paystack:     'Africa\'s leading payment gateway with support for cards, bank transfers, USSD, and mobile money.',
    square:       'Unified commerce solution with in-person and online payments, inventory management, and business analytics.',
    braintree:    'A PayPal service offering flexible payment integrations with support for cards, PayPal, Venmo, and local methods.',
    authorize:    'One of the most established payment gateways in the US, providing secure card processing and fraud detection.',
    ccavenue:     'India\'s largest payment gateway with 200+ payment options including cards, net banking, UPI, and wallets.',
    payu:         'Global payment platform present in 50+ markets, supporting local and international payment methods.',
    worldpay:     'A global leader in payment processing — accept cards and alternative payment methods across 146 countries.',
    klarna:       'Offer buy-now-pay-later, installment plans, and flexible financing options to boost conversions at checkout.',
    afterpay:     'Let customers split purchases into 4 interest-free installments, increasing average order value and conversion.',
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
        const res = await http.get(`${baseUrl}/payment-gateway-list`)
        plugins.value = res.data?.data ?? []
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { loading.value = false }
}

async function toggleStatus(plugin) {
    toggling.value = plugin.name
    try {
        const res = await http.post(`${baseUrl}/updatePaymentStatus`, {
            name:   plugin.name,
            status: plugin.status ? 0 : 1,
        })
        successHandler(res, COMPONENT)
        await loadPlugins()
    } catch (e) { errorHandler(e, COMPONENT) }
    finally { toggling.value = null }
}
</script>

