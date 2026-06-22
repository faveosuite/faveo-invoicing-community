jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '1' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))
jest.mock('@/core/composables/useDateTime', () => ({
    useDateTime: () => ({ formatDate: (v) => v ?? '' }),
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import OrderShow from '@/pages/client/orders/OrderShow.vue'

const orderFixture = {
    id: 1,
    number: 'ORD-001',
    order_date: '2025-01-01',
    status: 'Active',
    update_ends_at: '2026-01-01',
    license_ends_at: '2026-01-01',
    serial_key: 'ABC-DEF-GHI',
    is_cloud: false,
    is_terminated: false,
    whatsapp_enabled: false,
    autorenewal_enabled: false,
    is_subscribed: false,
    user: {
        name: 'John Doe',
        email: 'john@example.com',
        mobile: '1234567890',
        address: '123 Main St',
    },
}

describe('OrderShow.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/get-my-orders').reply(200, { data: orderFixture })

        wrapper = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card',
                    'app-alert',
                    'app-modal',
                    'data-table',
                    'action-button',
                    'alert',
                    'router-link',
                    'loader',
                    'renew-modal',
                    'whatsapp-panel',
                    'modal',
                    'select-field',
                    'client-field',
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

    it('calls GET /get-my-orders on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.some(r => r.url.includes('/get-my-orders'))).toBe(true)
    })

    it('sets order data after successful API call', async () => {
        await flushPromises()
        expect(wrapper.vm.order).toEqual(orderFixture)
        expect(wrapper.vm.loading).toBe(false)
    })

    it('sets loading to false when API returns 500', async () => {
        axiosMock.onGet('/get-my-orders').reply(500, { message: 'Server error' })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
        w.unmount()
    })

    it('calls errorHandler when API returns 500', async () => {
        axiosMock.onGet('/get-my-orders').reply(500, { message: 'Server error' })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('defaults to the license tab', async () => {
        await flushPromises()
        expect(wrapper.vm.activeTab).toBe('license')
    })

    it('showCloudTab is false when order.is_cloud is false', async () => {
        await flushPromises()
        expect(wrapper.vm.showCloudTab).toBe(false)
    })

    it('showAutoRenewTab is false when autorenewal_enabled is false', async () => {
        await flushPromises()
        expect(wrapper.vm.showAutoRenewTab).toBe(false)
    })

    it('invoiceBadge returns bg-success for paid status', async () => {
        await flushPromises()
        expect(wrapper.vm.invoiceBadge('paid')).toBe('bg-success')
    })

    it('invoiceBadge returns bg-danger for cancelled status', async () => {
        await flushPromises()
        expect(wrapper.vm.invoiceBadge('cancelled')).toBe('bg-danger')
    })

    it('paymentBadge returns badge bg-success for success status', async () => {
        await flushPromises()
        expect(wrapper.vm.paymentBadge('success')).toBe('badge bg-success')
    })

    it('showRenewModal defaults to false', () => {
        expect(wrapper.vm.showRenewModal).toBe(false)
    })

    it('shows alert warning when order data is null', async () => {
        axiosMock.onGet('/get-my-orders').reply(200, { data: null })

        const w = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-card', 'app-alert', 'app-modal', 'data-table',
                    'action-button', 'alert', 'router-link', 'loader',
                    'renew-modal', 'whatsapp-panel', 'modal',
                    'select-field', 'client-field',
                ],
            },
        })
        await flushPromises()
        expect(w.vm.order).toBeNull()
        w.unmount()
    })
})

// ── Additional coverage for uncovered functions ─────────────────────────────
describe('OrderShow.vue — modal and state helpers', () => {
    let wrapper
    let axiosMock

    const STUBS = [
        'app-card', 'app-alert', 'app-modal', 'data-table', 'action-button',
        'alert', 'router-link', 'loader', 'renew-modal', 'whatsapp-panel',
        'modal', 'select-field', 'client-field',
    ]

    beforeEach(async () => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/get-my-orders').reply(200, { data: orderFixture })
        wrapper = mount(OrderShow, { global: { plugins: [createTestingPinia()], stubs: STUBS } })
        await flushPromises()
        wrapper.vm.order = { ...orderFixture }
    })

    afterEach(() => { axiosMock.restore(); jest.clearAllMocks() })

    // ── Computed properties ──────────────────────────────────────────
    it('actionOptions contains increase and decrease entries', () => {
        const opts = wrapper.vm.actionOptions
        expect(opts.map(o => o.id)).toEqual(['increase', 'decrease'])
    })

    it('gatewayOptions shows all gateways when order has no available_gateways', () => {
        wrapper.vm.order = { ...orderFixture, available_gateways: [] }
        expect(wrapper.vm.gatewayOptions.map(g => g.id)).toEqual(['stripe', 'razorpay'])
    })

    it('gatewayOptions filters to available_gateways when set', () => {
        wrapper.vm.order = { ...orderFixture, available_gateways: ['stripe'] }
        expect(wrapper.vm.gatewayOptions.map(g => g.id)).toEqual(['stripe'])
    })

    it('showCloudTab is true when order is_cloud and not Terminated', () => {
        wrapper.vm.order = { ...orderFixture, is_cloud: true, status: 'Active' }
        expect(wrapper.vm.showCloudTab).toBe(true)
    })

    it('showCloudTab is false when order status is Terminated', () => {
        wrapper.vm.order = { ...orderFixture, is_cloud: true, status: 'Terminated' }
        expect(wrapper.vm.showCloudTab).toBe(false)
    })

    it('showAutoRenewTab is true when autorenewal_enabled is true', () => {
        wrapper.vm.order = { ...orderFixture, autorenewal_enabled: true }
        expect(wrapper.vm.showAutoRenewTab).toBe(true)
    })

    // ── Domain modal ─────────────────────────────────────────────────
    it('openDomainModal opens the domain modal and clears form', () => {
        wrapper.vm.domainForm.newDomain = 'old-domain'
        wrapper.vm.openDomainModal()
        expect(wrapper.vm.showDomainModal).toBe(true)
        expect(wrapper.vm.domainForm.newDomain).toBe('')
    })

    it('closeDomainModal closes the domain modal', () => {
        wrapper.vm.showDomainModal = true
        wrapper.vm.closeDomainModal()
        expect(wrapper.vm.showDomainModal).toBe(false)
    })

    it('submitDomain returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        await wrapper.vm.submitDomain()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitDomain posts to change/domain and closes modal on success', async () => {
        axiosMock.onPost('/change/domain').reply(200, { message: 'Changed' })
        wrapper.vm.cloud = { installation_path: 'old.com', serial_key: 'KEY', product_id: 1, order_id: 1 }
        wrapper.vm.domainForm.newDomain = 'new.com'
        await wrapper.vm.submitDomain()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/change/domain'))).toBe(true)
        expect(wrapper.vm.showDomainModal).toBe(false)
    })

    it('submitDomain calls errorHandler on failure', async () => {
        axiosMock.onPost('/change/domain').reply(500)
        wrapper.vm.cloud = { installation_path: 'old.com', serial_key: 'K', product_id: 1, order_id: 1 }
        wrapper.vm.domainForm.newDomain = 'new.com'
        await wrapper.vm.submitDomain()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    // ── Agents modal ─────────────────────────────────────────────────
    it('openAgentsModal opens the modal and resets agent form', () => {
        wrapper.vm.agentForm.number = '5'
        wrapper.vm.openAgentsModal()
        expect(wrapper.vm.showAgentsModal).toBe(true)
        expect(wrapper.vm.agentForm.number).toBe('')
    })

    it('closeAgentsModal closes the agents modal', () => {
        wrapper.vm.showAgentsModal = true
        wrapper.vm.closeAgentsModal()
        expect(wrapper.vm.showAgentsModal).toBe(false)
    })

    it('onActionChange updates agentForm.action', () => {
        wrapper.vm.onActionChange({ id: 'decrease' })
        expect(wrapper.vm.agentForm.action).toBe('decrease')
    })

    it('onActionChange defaults to increase when value is null', () => {
        wrapper.vm.onActionChange(null)
        expect(wrapper.vm.agentForm.action).toBe('increase')
    })

    it('fetchAgentCost clears agentCost when cloud is null', async () => {
        wrapper.vm.cloud = null
        wrapper.vm.agentCost = '100'
        await wrapper.vm.fetchAgentCost()
        expect(wrapper.vm.agentCost).toBe('')
    })

    it('fetchAgentCost clears agentCost when number is empty', async () => {
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.agentForm.number = ''
        await wrapper.vm.fetchAgentCost()
        expect(wrapper.vm.agentCost).toBe('')
    })

    it('fetchAgentCost posts and updates agentCost on success', async () => {
        axiosMock.onPost('/get-agent-inc-dec-cost').reply(200, { priceToPay: '$50' })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.agentForm.number = '10'
        await wrapper.vm.fetchAgentCost()
        await flushPromises()
        expect(wrapper.vm.agentCost).toBe('$50')
    })

    it('fetchAgentCost clears agentCost and calls errorHandler on failure', async () => {
        axiosMock.onPost('/get-agent-inc-dec-cost').reply(500)
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.agentForm.number = '10'
        await wrapper.vm.fetchAgentCost()
        await flushPromises()
        expect(wrapper.vm.agentCost).toBe('')
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submitAgents returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        await wrapper.vm.submitAgents()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitAgents posts to changeAgents and resets busy on completion', async () => {
        axiosMock.onPost('/changeAgents').reply(200, { data: {} })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1, product_id: 1, sub_id: 1 }
        wrapper.vm.agentForm.number = '10'
        await wrapper.vm.submitAgents()
        await flushPromises()
        expect(wrapper.vm.agentBusy).toBe(false)
    })

    it('submitAgents calls errorHandler on failure', async () => {
        axiosMock.onPost('/changeAgents').reply(500)
        wrapper.vm.cloud = { current_agents: 5, order_id: 1, product_id: 1, sub_id: 1 }
        wrapper.vm.agentForm.number = '10'
        await wrapper.vm.submitAgents()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    // ── Plan modal ───────────────────────────────────────────────────
    it('openPlanModal opens the modal and resets plan form', () => {
        wrapper.vm.planForm.planId = '5'
        wrapper.vm.openPlanModal()
        expect(wrapper.vm.showPlanModal).toBe(true)
        expect(wrapper.vm.planForm.planId).toBe('')
    })

    it('closePlanModal closes the plan modal', () => {
        wrapper.vm.showPlanModal = true
        wrapper.vm.closePlanModal()
        expect(wrapper.vm.showPlanModal).toBe(false)
    })

    it('onPlanChange updates planForm.planId', () => {
        wrapper.vm.onPlanChange({ id: 3 })
        expect(wrapper.vm.planForm.planId).toBe(3)
    })

    it('fetchPlanCost clears planCost when cloud is null', async () => {
        wrapper.vm.cloud = null
        await wrapper.vm.fetchPlanCost()
        expect(wrapper.vm.planCost).toBeNull()
    })

    it('fetchPlanCost fetches and sets planCost on success', async () => {
        axiosMock.onPost('/get-cloud-upgrade-cost').reply(200, { price_to_be_paid: '$20' })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = 2
        await wrapper.vm.fetchPlanCost()
        await flushPromises()
        expect(wrapper.vm.planCost).toEqual({ price_to_be_paid: '$20' })
    })

    it('submitPlan returns early when no planId', async () => {
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = ''
        await wrapper.vm.submitPlan()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitPlan posts to upgradeDowngradeCloud and resets busy', async () => {
        axiosMock.onPost('/upgradeDowngradeCloud').reply(200, { data: {} })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = 2
        await wrapper.vm.submitPlan()
        await flushPromises()
        expect(wrapper.vm.planBusy).toBe(false)
    })

    // ── Cloud tab (lazy load) ────────────────────────────────────────
    it('openCloudTab sets activeTab and fetches cloud settings', async () => {
        axiosMock.onGet('/get-cloud-settings/1').reply(200, { data: { installation_path: 'cloud.com' } })
        await wrapper.vm.openCloudTab()
        await flushPromises()
        expect(wrapper.vm.activeTab).toBe('cloud')
        expect(wrapper.vm.cloud).toEqual({ installation_path: 'cloud.com' })
        expect(wrapper.vm.cloudLoading).toBe(false)
    })

    it('openCloudTab skips fetch if already loaded', async () => {
        wrapper.vm.cloudLoaded = true
        await wrapper.vm.openCloudTab()
        expect(axiosMock.history.get.filter(r => r.url.includes('/get-cloud-settings')).length).toBe(0)
    })

    it('openCloudTab calls errorHandler on fetch failure', async () => {
        axiosMock.onGet('/get-cloud-settings/1').reply(500)
        await wrapper.vm.openCloudTab()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    // ── Copy license ─────────────────────────────────────────────────
    it('copyLicense returns early when serial_key is absent', async () => {
        wrapper.vm.order = { ...orderFixture, serial_key: '' }
        await wrapper.vm.copyLicense()
        expect(wrapper.vm.copied).toBe(false)
    })

    it('copyLicense sets copied=true on success then resets after timeout', async () => {
        jest.useFakeTimers()
        Object.assign(navigator, {
            clipboard: { writeText: jest.fn().mockResolvedValue(undefined) },
        })
        await wrapper.vm.copyLicense()
        expect(wrapper.vm.copied).toBe(true)
        jest.advanceTimersByTime(2001)
        expect(wrapper.vm.copied).toBe(false)
        jest.useRealTimers()
    })

    // ── Reissue license ──────────────────────────────────────────────
    it('reissueLicense patches reissue-license and increments installKey', async () => {
        axiosMock.onPatch('/reissue-license').reply(200, { message: 'Reissued' })
        const before = wrapper.vm.installKey
        await wrapper.vm.reissueLicense()
        await flushPromises()
        expect(wrapper.vm.installKey).toBe(before + 1)
        expect(wrapper.vm.reissuing).toBe(false)
    })

    it('reissueLicense calls errorHandler on failure', async () => {
        axiosMock.onPatch('/reissue-license').reply(500)
        await wrapper.vm.reissueLicense()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('reissueLicense returns early when already reissuing', async () => {
        wrapper.vm.reissuing = true
        await wrapper.vm.reissueLicense()
        expect(axiosMock.history.patch.length).toBe(0)
    })

    // ── Auto-renewal modals ──────────────────────────────────────────
    it('closeRenewalModal closes the renewal modal and clears gateway', () => {
        wrapper.vm.showRenewalModal = true
        wrapper.vm.selectedGateway = 'stripe'
        wrapper.vm.closeRenewalModal()
        expect(wrapper.vm.showRenewalModal).toBe(false)
        expect(wrapper.vm.selectedGateway).toBe('')
    })

    it('closeStripeRenewalModal closes the Stripe renewal modal', () => {
        wrapper.vm.showStripeRenewalModal = true
        wrapper.vm.stripeRenewalBusy = true
        wrapper.vm.closeStripeRenewalModal()
        expect(wrapper.vm.showStripeRenewalModal).toBe(false)
        expect(wrapper.vm.stripeRenewalBusy).toBe(false)
    })

    it('closeDisableRenewalModal closes the disable renewal modal', () => {
        wrapper.vm.showDisableRenewalModal = true
        wrapper.vm.closeDisableRenewalModal()
        expect(wrapper.vm.showDisableRenewalModal).toBe(false)
    })

    it('confirmDisableRenewal posts to disable endpoint and updates order state', async () => {
        axiosMock.onPost('/auto-renewal/1/disable').reply(200, { message: 'Disabled' })
        wrapper.vm.order = { ...orderFixture, is_subscribed: true }
        await wrapper.vm.confirmDisableRenewal()
        await flushPromises()
        expect(wrapper.vm.order.is_subscribed).toBe(false)
        expect(wrapper.vm.renewalBusy).toBe(false)
    })

    it('confirmDisableRenewal calls errorHandler on failure', async () => {
        axiosMock.onPost('/auto-renewal/1/disable').reply(500)
        wrapper.vm.order = { ...orderFixture, is_subscribed: true }
        await wrapper.vm.confirmDisableRenewal()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('enableAutoRenewal returns early when no gateway selected', async () => {
        wrapper.vm.selectedGateway = ''
        await wrapper.vm.enableAutoRenewal()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('enableAutoRenewal with razorpay calls the razorpay order API', async () => {
        axiosMock.onPost('/auto-renewal/1/razorpay/order').reply(500)
        wrapper.vm.selectedGateway = 'razorpay'
        await wrapper.vm.enableAutoRenewal()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/razorpay/order'))).toBe(true)
        expect(wrapper.vm.renewalBusy).toBe(false)
    })

    it('enableAutoRenewal with stripe calls the stripe session API', async () => {
        axiosMock.onPost('/auto-renewal/1/stripe/session').reply(500)
        wrapper.vm.selectedGateway = 'stripe'
        await wrapper.vm.enableAutoRenewal()
        await flushPromises()
        expect(axiosMock.history.post.some(r => r.url.includes('/stripe/session'))).toBe(true)
        expect(wrapper.vm.renewalBusy).toBe(false)
    })

    // ── payRenewalStripe — card validation ───────────────────────────
    it('payRenewalStripe sets errors on empty card fields and returns', async () => {
        wrapper.vm.renewalCardComplete.number = false
        wrapper.vm.renewalCardComplete.expiry = false
        wrapper.vm.renewalCardComplete.cvc    = false
        await wrapper.vm.payRenewalStripe()
        expect(wrapper.vm.renewalCardErrors.number).toBeTruthy()
        expect(wrapper.vm.renewalCardErrors.expiry).toBeTruthy()
        expect(wrapper.vm.renewalCardErrors.cvc).toBeTruthy()
    })

    it('payRenewalStripe does not confirm when card is incomplete', async () => {
        wrapper.vm.renewalCardComplete.number = false
        await wrapper.vm.payRenewalStripe()
        expect(axiosMock.history.post.length).toBe(0)
    })

    // ── finalizeRenewalStripe ────────────────────────────────────────
    it('finalizeRenewalStripe posts to stripe/confirm and updates is_subscribed on success', async () => {
        axiosMock.onPost('/auto-renewal/1/stripe/confirm').reply(200, { message: 'Done' })
        wrapper.vm.order = { ...orderFixture, is_subscribed: false }
        await wrapper.vm.finalizeRenewalStripe()
        await flushPromises()
        expect(wrapper.vm.order.is_subscribed).toBe(true)
        expect(wrapper.vm.showStripeRenewalModal).toBe(false)
    })

    it('finalizeRenewalStripe closes modal and sets alert on failure', async () => {
        axiosMock.onPost('/auto-renewal/1/stripe/confirm').reply(500)
        wrapper.vm.showStripeRenewalModal = true
        await wrapper.vm.finalizeRenewalStripe()
        await flushPromises()
        expect(wrapper.vm.showStripeRenewalModal).toBe(false)
    })

    // ── loadScript ───────────────────────────────────────────────────
    it('loadScript resolves immediately for already-loaded scripts', async () => {
        const src = 'https://checkout.razorpay.com/v1/checkout.js'
        const s = document.createElement('script')
        s.src = src
        document.head.appendChild(s)
        await expect(wrapper.vm.loadScript(src)).resolves.toBeUndefined()
    })

    // ── invoiceBadge remaining branches ─────────────────────────────
    it('invoiceBadge returns bg-warning for pending status', () => {
        expect(wrapper.vm.invoiceBadge('pending')).toBe('bg-warning text-dark')
    })

    it('invoiceBadge returns bg-info for partially paid status', () => {
        expect(wrapper.vm.invoiceBadge('partially paid')).toBe('bg-info text-dark')
    })

    it('invoiceBadge returns bg-secondary for unknown status', () => {
        expect(wrapper.vm.invoiceBadge('unknown')).toBe('bg-secondary')
    })

    // ── paymentBadge remaining branches ─────────────────────────────
    it('paymentBadge returns badge bg-warning for pending', () => {
        expect(wrapper.vm.paymentBadge('pending')).toBe('badge bg-warning text-dark')
    })

    it('paymentBadge returns badge bg-danger for failed', () => {
        expect(wrapper.vm.paymentBadge('failed')).toBe('badge bg-danger')
    })

    it('paymentBadge returns badge bg-secondary for unknown', () => {
        expect(wrapper.vm.paymentBadge('unknown')).toBe('badge bg-secondary')
    })

    // ── openCloudTab ─────────────────────────────────────────────────────────
    it('openCloudTab loads cloud data on success', async () => {
        const cloudData = { plan: 'starter', order_id: 1 }
        axiosMock.onGet('/get-cloud-settings/1').reply(200, { data: cloudData })
        await flushPromises()
        await wrapper.vm.openCloudTab()
        await flushPromises()
        expect(wrapper.vm.cloud).toEqual(cloudData)
        expect(wrapper.vm.cloudLoading).toBe(false)
        expect(wrapper.vm.cloudLoaded).toBe(true)
    })

    it('openCloudTab does not re-fetch when cloudLoaded is already true', async () => {
        axiosMock.resetHistory()
        wrapper.vm.cloudLoaded = true
        await wrapper.vm.openCloudTab()
        await flushPromises()
        expect(axiosMock.history.get.filter(r => r.url && r.url.includes('cloud-settings')).length).toBe(0)
    })

    it('openCloudTab sets cloudLoading to false on error', async () => {
        wrapper.vm.cloudLoaded = false
        axiosMock.onGet('/get-cloud-settings/1').reply(500)
        await wrapper.vm.openCloudTab()
        await flushPromises()
        expect(wrapper.vm.cloudLoading).toBe(false)
    })

    // ── Domain modal ──────────────────────────────────────────────────────────
    it('openDomainModal sets showDomainModal=true', () => {
        wrapper.vm.openDomainModal()
        expect(wrapper.vm.showDomainModal).toBe(true)
    })

    it('closeDomainModal sets showDomainModal=false', () => {
        wrapper.vm.showDomainModal = true
        wrapper.vm.closeDomainModal()
        expect(wrapper.vm.showDomainModal).toBe(false)
    })

    it('submitDomain returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        axiosMock.resetHistory()
        await wrapper.vm.submitDomain()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitDomain posts new domain and closes modal on success', async () => {
        axiosMock.onPost('/change/domain').reply(200, { message: 'ok' })
        wrapper.vm.cloud = { installation_path: '/old', serial_key: 'KEY', product_id: 1, order_id: 1 }
        wrapper.vm.showDomainModal = true
        wrapper.vm.domainForm.newDomain = 'new.example.com'
        await wrapper.vm.submitDomain()
        await flushPromises()
        expect(wrapper.vm.showDomainModal).toBe(false)
        expect(wrapper.vm.domainBusy).toBe(false)
    })

    it('submitDomain handles API error', async () => {
        axiosMock.onPost('/change/domain').reply(422, { message: 'Invalid' })
        wrapper.vm.cloud = { installation_path: '/old', serial_key: 'KEY', product_id: 1, order_id: 1 }
        wrapper.vm.showDomainModal = true
        await wrapper.vm.submitDomain()
        await flushPromises()
        expect(wrapper.vm.domainBusy).toBe(false)
    })

    // ── Agents modal ──────────────────────────────────────────────────────────
    it('openAgentsModal sets showAgentsModal=true', () => {
        wrapper.vm.openAgentsModal()
        expect(wrapper.vm.showAgentsModal).toBe(true)
    })

    it('closeAgentsModal sets showAgentsModal=false', () => {
        wrapper.vm.showAgentsModal = true
        wrapper.vm.closeAgentsModal()
        expect(wrapper.vm.showAgentsModal).toBe(false)
    })

    it('fetchAgentCost returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        wrapper.vm.agentForm.number = '2'
        axiosMock.resetHistory()
        await wrapper.vm.fetchAgentCost()
        expect(axiosMock.history.post.filter(r => r.url && r.url.includes('agent')).length).toBe(0)
    })

    it('fetchAgentCost calls the cost API and sets agentCost', async () => {
        axiosMock.onPost('/get-agent-inc-dec-cost').reply(200, { priceToPay: '$10' })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.agentForm.number = '2'
        wrapper.vm.agentForm.action = 'increase'
        await wrapper.vm.fetchAgentCost()
        await flushPromises()
        expect(wrapper.vm.agentCost).toBe('$10')
    })

    it('fetchAgentCost returns early when number is empty', async () => {
        axiosMock.resetHistory()
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.agentForm.number = ''
        await wrapper.vm.fetchAgentCost()
        expect(axiosMock.history.post.filter(r => r.url && r.url.includes('agent')).length).toBe(0)
    })

    it('submitAgents returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        axiosMock.resetHistory()
        await wrapper.vm.submitAgents()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitAgents posts and sets agentBusy=false on success', async () => {
        axiosMock.onPost('/changeAgents').reply(200, { data: {} })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1, product_id: 1, sub_id: 'S1' }
        wrapper.vm.agentForm.number = '3'
        await wrapper.vm.submitAgents()
        await flushPromises()
        expect(wrapper.vm.agentBusy).toBe(false)
    })

    it('submitAgents handles API error', async () => {
        axiosMock.onPost('/changeAgents').reply(500)
        wrapper.vm.cloud = { current_agents: 5, order_id: 1, product_id: 1, sub_id: 'S1' }
        wrapper.vm.agentForm.number = '3'
        await wrapper.vm.submitAgents()
        await flushPromises()
        expect(wrapper.vm.agentBusy).toBe(false)
    })

    // ── Plan modal ────────────────────────────────────────────────────────────
    it('openPlanModal sets showPlanModal=true', () => {
        wrapper.vm.openPlanModal()
        expect(wrapper.vm.showPlanModal).toBe(true)
    })

    it('closePlanModal sets showPlanModal=false', () => {
        wrapper.vm.showPlanModal = true
        wrapper.vm.closePlanModal()
        expect(wrapper.vm.showPlanModal).toBe(false)
    })

    it('fetchPlanCost returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        wrapper.vm.planForm.planId = '5'
        axiosMock.resetHistory()
        await wrapper.vm.fetchPlanCost()
        expect(axiosMock.history.post.filter(r => r.url && r.url.includes('plan')).length).toBe(0)
    })

    it('fetchPlanCost calls cost API and sets planCost', async () => {
        axiosMock.onPost('/get-cloud-upgrade-cost').reply(200, { priceTotal: 100 })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = '5'
        await wrapper.vm.fetchPlanCost()
        await flushPromises()
        expect(wrapper.vm.planCost).not.toBeNull()
    })

    it('fetchPlanCost returns early when planId is empty', async () => {
        axiosMock.resetHistory()
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = ''
        await wrapper.vm.fetchPlanCost()
        expect(axiosMock.history.post.filter(r => r.url && r.url.includes('plan')).length).toBe(0)
    })

    it('submitPlan returns early when cloud is null', async () => {
        wrapper.vm.cloud = null
        axiosMock.resetHistory()
        await wrapper.vm.submitPlan()
        expect(axiosMock.history.post.length).toBe(0)
    })

    it('submitPlan posts and sets planBusy=false on success', async () => {
        axiosMock.onPost('/upgradeDowngradeCloud').reply(200, { data: {} })
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = '5'
        await wrapper.vm.submitPlan()
        await flushPromises()
        expect(wrapper.vm.planBusy).toBe(false)
    })

    it('submitPlan handles API error', async () => {
        axiosMock.onPost('/upgradeDowngradeCloud').reply(500)
        wrapper.vm.cloud = { current_agents: 5, order_id: 1 }
        wrapper.vm.planForm.planId = '5'
        await wrapper.vm.submitPlan()
        await flushPromises()
        expect(wrapper.vm.planBusy).toBe(false)
    })

    // ── Computed branches ─────────────────────────────────────────────────────
    it('showCloudTab is true when order is_cloud and not Terminated', async () => {
        await flushPromises()
        wrapper.vm.order = { ...orderFixture, is_cloud: true, status: 'Active' }
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.showCloudTab).toBe(true)
    })

    it('showCloudTab is false when order status is Terminated', async () => {
        await flushPromises()
        wrapper.vm.order = { ...orderFixture, is_cloud: true, status: 'Terminated' }
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.showCloudTab).toBe(false)
    })

    it('showAutoRenewTab is true when autorenewal_enabled', async () => {
        await flushPromises()
        wrapper.vm.order = { ...orderFixture, autorenewal_enabled: true }
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.showAutoRenewTab).toBe(true)
    })

    it('gatewayOptions returns lowercase gateway list', async () => {
        await flushPromises()
        wrapper.vm.order = { ...orderFixture, available_gateways: ['Stripe', 'Razorpay'] }
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.gatewayOptions).toContainEqual(expect.objectContaining({ id: 'stripe' }))
    })

    it('gatewayOptions returns all gateways when available_gateways is empty', async () => {
        await flushPromises()
        wrapper.vm.order = { ...orderFixture, available_gateways: [] }
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.gatewayOptions.length).toBeGreaterThan(0)
    })

    it('onActionChange sets agentForm.action from object with id', () => {
        wrapper.vm.onActionChange({ id: 'decrease' })
        expect(wrapper.vm.agentForm.action).toBe('decrease')
    })

    it('onActionChange defaults to increase when passed null', () => {
        wrapper.vm.onActionChange(null)
        expect(wrapper.vm.agentForm.action).toBe('increase')
    })

    it('onPlanChange sets planForm.planId from object with id', () => {
        wrapper.vm.onPlanChange({ id: '7' })
        expect(wrapper.vm.planForm.planId).toBe('7')
    })

    it('onPlanChange defaults to empty string when passed null', () => {
        wrapper.vm.onPlanChange(null)
        expect(wrapper.vm.planForm.planId).toBe('')
    })
})
