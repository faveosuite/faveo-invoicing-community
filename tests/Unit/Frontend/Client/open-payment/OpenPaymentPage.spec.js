jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/openPaymentSchema', () => ({ openPaymentSchema: {} }))
jest.mock('@recaptcha', () => ({
    RecaptchaField: {
        template: '<div />',
        props: ['action'],
        methods: {
            getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'token' }),
            reset: jest.fn(),
            triggerFallback: jest.fn(),
        },
    },
}), { virtual: true })
jest.mock('@/core/composables/useBreadcrumb.js', () => ({ setPageTitle: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import OpenPaymentPage from '@/pages/client/open-payment/OpenPaymentPage.vue'

describe('OpenPaymentPage.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/pay/config').reply(200, {
            data: {
                app_title: 'Test App',
                currencies: [{ code: 'USD', symbol: '$', name: 'United States dollar' }],
                gateways: [{ name: 'Razorpay', processing_fee: 0 }],
            },
        })
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: null } })

        wrapper = mount(OpenPaymentPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-alert',
                    'app-modal',
                    'client-field',
                    'phone-field',
                    'dynamic-select',
                    'select-field',
                    'recaptcha-field',
                    'global-loader',
                    'router-link',
                ],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the stepper with 3 steps', () => {
        const steps = wrapper.findAll('.step-item')
        expect(steps.length).toBe(3)
    })

    it('starts on the form step', () => {
        expect(wrapper.find('[key="form"]').exists() || wrapper.find('form').exists()).toBeTruthy()
    })

    it('loads config on mount and calls GET /pay/config', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/pay/config'))).toBe(true)
    })

    it('falls back to defaults when /pay/config returns 500', async () => {
        axiosMock.onGet('/pay/config').reply(500)
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: null } })

        const w = mount(OpenPaymentPage, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-alert', 'app-modal', 'client-field', 'phone-field',
                    'dynamic-select', 'select-field', 'recaptcha-field',
                    'global-loader', 'router-link',
                ],
            },
        })
        await flushPromises()
        // Component should still exist after error fallback
        expect(w.exists()).toBeTruthy()
        w.unmount()
    })

    it('shows a submit button on the form step', () => {
        const btn = wrapper.find('button[type="submit"]')
        expect(btn.exists()).toBeTruthy()
    })

    it('calls validateForm on form submit', async () => {
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(validateForm).toHaveBeenCalled()
    })

    it('does not advance to summary step when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.step).toBe('form')
    })

    it('advances to summary step when validation passes', async () => {
        axiosMock.onGet('/pay/calculate').reply(200, {
            data: { base_amount: '100.00', processing_fee: '0.00', processing_fee_rate: 0, total: '100.00' },
        })
        validateForm.mockResolvedValueOnce(true)
        await flushPromises()
        await wrapper.find('form').trigger('submit')
        await flushPromises()
        expect(wrapper.vm.step).toBe('summary')
    })
})

// ── Extended coverage ───────────────────────────────────────────────────────
describe('OpenPaymentPage.vue — extended coverage', () => {
    let wrapper
    let axiosMock

    const STUBS = [
        'app-alert', 'app-modal', 'client-field', 'phone-field',
        'dynamic-select', 'select-field', 'recaptcha-field',
        'global-loader', 'router-link',
    ]

    beforeEach(async () => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/pay/config').reply(200, {
            data: {
                app_title: 'Test App',
                currencies: [{ code: 'USD', symbol: '$', name: 'United States dollar' }],
                gateways: [{ name: 'Razorpay', processing_fee: 0 }, { name: 'Stripe', processing_fee: 0 }],
            },
        })
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: null } })

        wrapper = mount(OpenPaymentPage, {
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
    })

    afterEach(() => { axiosMock.restore(); jest.clearAllMocks() })

    // ── Computed properties ──────────────────────────────────────────
    it('steps computed returns 3 step objects', () => {
        expect(wrapper.vm.steps).toHaveLength(3)
    })

    it('progressSteps marks the active step correctly', () => {
        expect(wrapper.vm.progressSteps.find(s => s.key === 'form')?.active).toBe(true)
    })

    it('selectedCurrency returns the matching currency option', () => {
        expect(wrapper.vm.selectedCurrency?.code).toBe('USD')
    })

    it('selectedCurrencySymbol returns the symbol for the selected currency', () => {
        expect(wrapper.vm.selectedCurrencySymbol).toBe('$')
    })

    it('avatarInitials returns two letters for a full name', () => {
        wrapper.vm.form.name = 'John Doe'
        expect(wrapper.vm.avatarInitials).toBe('JD')
    })

    it('avatarInitials returns one letter for a single-word name', () => {
        wrapper.vm.form.name = 'Alice'
        expect(wrapper.vm.avatarInitials).toBe('A')
    })

    // ── State change helpers ─────────────────────────────────────────
    it('onCurrencyChange updates form.currency', () => {
        wrapper.vm.onCurrencyChange({ code: 'EUR' })
        expect(wrapper.vm.form.currency).toBe('EUR')
    })

    it('onCurrencyChange clears currency when value is null', () => {
        wrapper.vm.onCurrencyChange(null)
        expect(wrapper.vm.form.currency).toBe('')
    })

    it('onCountryChange updates selectedCountryObj and resets state', () => {
        wrapper.vm.onCountryChange({ code: 'IN', name: 'India' })
        expect(wrapper.vm.selectedCountryObj).toEqual({ code: 'IN', name: 'India' })
        expect(wrapper.vm.form.country).toBe('IN')
        expect(wrapper.vm.selectedStateObj).toBeNull()
        expect(wrapper.vm.form.state).toBe('')
    })

    it('onStateChange updates selectedStateObj and form.state', () => {
        wrapper.vm.onStateChange({ name: 'Maharashtra' })
        expect(wrapper.vm.form.state).toBe('Maharashtra')
        expect(wrapper.vm.selectedStateObj).toEqual({ name: 'Maharashtra' })
    })

    it('onStateChange clears state when value is null', () => {
        wrapper.vm.onStateChange(null)
        expect(wrapper.vm.form.state).toBe('')
    })

    // ── Navigation helpers ───────────────────────────────────────────
    it('backToForm resets step to form and clears order', () => {
        wrapper.vm.step = 'summary'
        wrapper.vm.order = { id: 1 }
        wrapper.vm.backToForm()
        expect(wrapper.vm.step).toBe('form')
        expect(wrapper.vm.order).toBeNull()
    })

    it('reset resets all state to initial values', () => {
        wrapper.vm.step = 'result'
        wrapper.vm.order = { id: 1 }
        wrapper.vm.showStripeModal = true
        wrapper.vm.reset()
        expect(wrapper.vm.step).toBe('form')
        expect(wrapper.vm.order).toBeNull()
        expect(wrapper.vm.showStripeModal).toBe(false)
    })

    it('showResult sets step to result with success=true', () => {
        const paid = { transaction_id: 'TXN-001', currency: 'USD', amount: '99', gateway: 'Stripe', description: 'Test' }
        wrapper.vm.showResult(true, paid)
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(true)
        expect(wrapper.vm.result.transactionId).toBe('TXN-001')
    })

    it('showResult sets step to result with success=false', () => {
        wrapper.vm.showResult(false, null, 'Payment declined')
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(false)
        expect(wrapper.vm.result.message).toBe('Payment declined')
    })

    it('onStripeModalClose resets Stripe modal state', () => {
        wrapper.vm.showStripeModal = true
        wrapper.vm.stripeLoading = true
        wrapper.vm.stripeSubmitting = true
        wrapper.vm.onStripeModalClose()
        expect(wrapper.vm.showStripeModal).toBe(false)
        expect(wrapper.vm.stripeLoading).toBe(false)
        expect(wrapper.vm.stripeSubmitting).toBe(false)
    })

    // ── onLogoError ──────────────────────────────────────────────────
    it('onLogoError replaces broken img with a text span', () => {
        const img = document.createElement('img')
        document.body.appendChild(img)
        const span = document.createElement('span')
        span.replaceWith = jest.fn()
        img.replaceWith = jest.fn()
        wrapper.vm.onLogoError({ target: img }, 'Razorpay')
        expect(img.replaceWith).toHaveBeenCalled()
    })

    // ── fetchCalculation ─────────────────────────────────────────────
    it('fetchCalculation returns early when amount is empty', async () => {
        wrapper.vm.form.amount = ''
        wrapper.vm.form.gateway = 'Razorpay'
        await wrapper.vm.fetchCalculation()
        expect(axiosMock.history.get.filter(r => r.url.includes('/calculate')).length).toBe(0)
    })

    it('fetchCalculation returns early when gateway is empty', async () => {
        wrapper.vm.form.amount = '100'
        wrapper.vm.form.gateway = ''
        await wrapper.vm.fetchCalculation()
        expect(axiosMock.history.get.filter(r => r.url.includes('/calculate')).length).toBe(0)
    })

    it('fetchCalculation fetches and updates calculation on success', async () => {
        axiosMock.onGet('/pay/calculate').reply(200, {
            data: { base_amount: '100.00', processing_fee: '2.00', processing_fee_rate: 2, total: '102.00' },
        })
        wrapper.vm.form.amount = '100'
        wrapper.vm.form.gateway = 'Razorpay'
        await wrapper.vm.fetchCalculation()
        await flushPromises()
        expect(wrapper.vm.calculation.total).toBe('102.00')
    })

    it('fetchCalculation silently ignores errors', async () => {
        axiosMock.onGet('/pay/calculate').reply(500)
        wrapper.vm.form.amount = '100'
        wrapper.vm.form.gateway = 'Razorpay'
        await expect(wrapper.vm.fetchCalculation()).resolves.not.toThrow()
    })

    // ── loadScript ───────────────────────────────────────────────────
    it('loadScript resolves immediately for already-loaded scripts', async () => {
        const src = 'https://checkout.razorpay.com/v1/checkout.js'
        const s = document.createElement('script')
        s.src = src
        document.head.appendChild(s)
        await expect(wrapper.vm.loadScript(src)).resolves.toBeUndefined()
    })

    // ── createOrder ──────────────────────────────────────────────────
    it('createOrder posts to /pay/create and sets order', async () => {
        axiosMock.onPost('/pay/create').reply(200, { data: { order: { id: 99, amount: '100', currency: 'USD', gateway: 'Razorpay' } } })
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'tok' }), disabled: false }
        await wrapper.vm.createOrder()
        await flushPromises()
        expect(wrapper.vm.order).toEqual(expect.objectContaining({ id: 99 }))
    })

    it('createOrder throws captcha error when captcha token is missing and not disabled', async () => {
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({}), disabled: false }
        await expect(wrapper.vm.createOrder()).rejects.toMatchObject({ captchaFailed: true })
    })

    // ── payStripe — card validation ───────────────────────────────────
    it('payStripe sets errors on incomplete card fields and returns', async () => {
        wrapper.vm.cardComplete.number = false
        wrapper.vm.cardComplete.expiry = false
        wrapper.vm.cardComplete.cvc = false
        await wrapper.vm.payStripe()
        expect(wrapper.vm.cardErrors.number).toBeTruthy()
    })

    it('payStripe returns immediately when stripeSubmitting is true', async () => {
        wrapper.vm.stripeSubmitting = true
        await wrapper.vm.payStripe()
        expect(wrapper.vm.cardErrors.number).toBe('')
    })

    // ── verifyRazorpay ───────────────────────────────────────────────
    it('verifyRazorpay posts and calls showResult on success', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(200, {
            success: true,
            data: { order: { id: 1, transaction_id: 'TXN-001', currency: 'USD', amount: '100', gateway: 'Razorpay', description: '' } },
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({ razorpay_payment_id: 'p', razorpay_order_id: 'o', razorpay_signature: 's' })
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(true)
    })

    it('verifyRazorpay shows failure result on success=false response', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(200, { success: false, message: 'Failed' })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({})
        await flushPromises()
        expect(wrapper.vm.result.success).toBe(false)
    })

    it('verifyRazorpay shows failure result on API error', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(500, { message: 'Server error' })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({})
        await flushPromises()
        expect(wrapper.vm.result.success).toBe(false)
    })

    // ── verifyStripe ─────────────────────────────────────────────────
    it('verifyStripe posts and calls showResult on success', async () => {
        axiosMock.onPost('/pay/verify/stripe').reply(200, {
            success: true,
            data: { order: { id: 1, transaction_id: 'PI-001', currency: 'USD', amount: '100', gateway: 'Stripe', description: '' } },
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyStripe('PI-001')
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(true)
    })

    it('verifyStripe shows failure result on API error', async () => {
        axiosMock.onPost('/pay/verify/stripe').reply(500)
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyStripe('PI-001')
        await flushPromises()
        expect(wrapper.vm.result.success).toBe(false)
    })

    // ── payNow ───────────────────────────────────────────────────────
    it('payNow with existing order and non-Stripe gateway calls POST /pay/prepare', async () => {
        axiosMock.onPost('/pay/prepare').reply(500)
        wrapper.vm.order = { id: 1 }
        wrapper.vm.form.gateway = 'Razorpay'
        await wrapper.vm.payNow()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/pay/prepare'))).toBe(true)
        expect(wrapper.vm.paying).toBe(false)
    })

    it('payNow with existing order and Stripe gateway calls initStripe', async () => {
        axiosMock.onPost('/pay/stripe/card-session').reply(500)
        wrapper.vm.order = { id: 1 }
        wrapper.vm.form.gateway = 'Stripe'
        await wrapper.vm.payNow()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/stripe/card-session'))).toBe(true)
    })

    it('payNow createOrder failure with generic error sets alert and paying=false', async () => {
        axiosMock.onPost('/pay/create').reply(500, { message: 'Server error' })
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'tok' }), disabled: false }
        wrapper.vm.order = null
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.paying).toBe(false)
    })

    it('payNow createOrder failure with 422 validation error redirects to form', async () => {
        axiosMock.onPost('/pay/create').reply(422, { errors: { name: ['Required'] }, message: 'Validation failed' })
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'tok' }), disabled: false }
        wrapper.vm.order = null
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.step).toBe('form')
        expect(wrapper.vm.paying).toBe(false)
    })

    // ── autoDetectCountry ────────────────────────────────────────────
    it('autoDetectCountry sets country when backend returns one', async () => {
        axiosMock.onGet('/pay/detect-country').reply(200, { data: { country: { code: 'IN', name: 'India' } } })
        await wrapper.vm.autoDetectCountry()
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('IN')
    })

    it('autoDetectCountry silently ignores errors', async () => {
        axiosMock.onGet('/pay/detect-country').reply(500)
        await expect(wrapper.vm.autoDetectCountry()).resolves.not.toThrow()
    })

    // ── verifyRazorpay ───────────────────────────────────────────────
    it('verifyRazorpay posts to /pay/verify/razorpay and sets step=result on success', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(200, {
            success: true,
            data: { order: { id: 1 } },
            message: 'ok',
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({ razorpay_payment_id: 'pay_123', razorpay_order_id: 'ord_123', razorpay_signature: 'sig' })
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(true)
    })

    it('verifyRazorpay sets result.success=false when API returns success=false', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(200, {
            success: false,
            data: {},
            message: 'Verification failed',
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({ razorpay_payment_id: 'pay_123' })
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(false)
    })

    it('verifyRazorpay handles API error and sets step=result', async () => {
        axiosMock.onPost('/pay/verify/razorpay').reply(500)
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyRazorpay({ razorpay_payment_id: 'pay_123' })
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(false)
    })

    // ── verifyStripe ─────────────────────────────────────────────────
    it('verifyStripe posts to /pay/verify/stripe and sets step=result on success', async () => {
        axiosMock.onPost('/pay/verify/stripe').reply(200, {
            success: true,
            data: { order: { id: 1 } },
            message: 'ok',
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyStripe('pi_test123')
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(true)
    })

    it('verifyStripe sets result.success=false on API failure', async () => {
        axiosMock.onPost('/pay/verify/stripe').reply(500, { message: 'Failed' })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyStripe('pi_test123')
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(false)
    })

    it('verifyStripe sets result.success=false when API returns success=false', async () => {
        axiosMock.onPost('/pay/verify/stripe').reply(200, {
            success: false,
            data: {},
            message: 'Verification failed',
        })
        wrapper.vm.order = { id: 1 }
        await wrapper.vm.verifyStripe('pi_test123')
        await flushPromises()
        expect(wrapper.vm.step).toBe('result')
        expect(wrapper.vm.result.success).toBe(false)
    })

    // ── payNow additional branches ───────────────────────────────────
    it('payNow with existing order routes to Stripe when gateway is Stripe', async () => {
        axiosMock.onPost('/pay/stripe/card-session').reply(500) // initStripe will fail but that's ok
        wrapper.vm.order = { id: 1, amount: '100.00' }
        wrapper.vm.form.gateway = 'Stripe'
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.paying).toBe(false)
    })

    it('payNow routes to Razorpay when gateway is not Stripe', async () => {
        axiosMock.onPost('/pay/prepare').reply(200, { data: { key: 'rzp_key' } })
        wrapper.vm.order = { id: 1, amount: '100.00' }
        wrapper.vm.calculation = { total: '100.00' }
        wrapper.vm.form.gateway = 'Razorpay'
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.paying).toBe(false)
    })

    it('payNow handles 429 rate-limit error on createOrder', async () => {
        axiosMock.onPost('/pay/create').reply(429, { message: 'Too many attempts' })
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'tok' }), disabled: false }
        wrapper.vm.order = null
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.paying).toBe(false)
    })

    it('payNow handles generic server error on createOrder', async () => {
        axiosMock.onPost('/pay/create').reply(500, { message: 'Server error' })
        wrapper.vm.captchaRef = { getPayload: () => Promise.resolve({ 'g-recaptcha-response': 'tok' }), disabled: false }
        wrapper.vm.order = null
        await wrapper.vm.payNow()
        await flushPromises()
        expect(wrapper.vm.paying).toBe(false)
    })

    // ── onStripeModalClose ───────────────────────────────────────────
    it('onStripeModalClose resets Stripe state', () => {
        wrapper.vm.showStripeModal = true
        wrapper.vm.stripeLoading = true
        wrapper.vm.stripeSubmitting = true
        wrapper.vm.onStripeModalClose()
        expect(wrapper.vm.showStripeModal).toBe(false)
        expect(wrapper.vm.stripeLoading).toBe(false)
        expect(wrapper.vm.stripeSubmitting).toBe(false)
    })
})
