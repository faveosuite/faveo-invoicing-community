jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockReplace = jest.fn()
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn(), replace: mockReplace }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>', name: 'RouterLink' },
}))
jest.mock('@/validations/admin/orderValidations', () => ({ licenseDetailsSchema: {} }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v, formatDateTime: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OrderShow from '@/pages/admin/orders/OrderShow.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const orderFixture = {
    order: {
        id: 0,
        number: 'ORD-0001',
        order_status: 'executed',
        created_at: '2026-01-01',
        license_mode: 'API',
        user: { id: 1, first_name: 'Jane', last_name: 'Doe', email: 'jane@example.com' },
        product_relation: { id: 10, name: 'Test Product' },
    },
    license_details: {
        licence_code: 'LIC-ABC123',
        installation_limit: 5,
        expiry_dates: {
            update_end:       { date: '2027-01-01', status: null },
            subscription_end: { date: '2028-01-01', status: null },
            support_end:      { date: '2027-06-01', status: null },
        },
    },
    is_subscribed: 0,
    payment_log: null,
}

describe('OrderShow.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/order\/0/).reply(200, { data: orderFixture })
        globalThis.mockHttp.onGet(/\/get-installation-details\/0/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/getOrderInvoices\/0/).reply(200, { data: [] })
        globalThis.mockHttp.onGet(/\/getOrderPayments\/0/).reply(200, { data: [] })
        globalThis.mockHttp.onPatch(/\/reissue-license/).reply(200, { data: { message: 'Reissued' } })
        globalThis.mockHttp.onPost(/\/auto-renewal\/\d+\/disable/).reply(200, { data: { message: 'Disabled' } })
        globalThis.mockHttp.onPost(/\/switch-license-mode/).reply(200, { data: { message: 'Switched' } })
        globalThis.mockHttp.onPost(/\/update-license-details/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'AppModal', 'DataTable', 'DeleteModal', 'action-button',
                    'inline-loader', 'loader', 'Switch', 'Tooltip', 'DatePicker',
                    'RouterLink',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches order data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/order\/0/.test(r.url))).toBe(true)
    })

    it('populates order data after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.order).not.toBeNull()
        expect(wrapper.vm.order.number).toBe('ORD-0001')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when order fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/order\/0/).reply(500)
        wrapper = mount(OrderShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'AppModal', 'DataTable', 'DeleteModal', 'action-button',
                    'inline-loader', 'loader', 'Switch', 'Tooltip', 'DatePicker',
                    'RouterLink',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('reissueLicense sends PATCH /reissue-license', async () => {
        await flushPromises()
        await wrapper.vm.reissueLicense()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => /\/reissue-license/.test(r.url))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler when reissueLicense fails', async () => {
        await flushPromises()
        globalThis.mockHttp.onPatch(/\/reissue-license/).reply(500)
        await wrapper.vm.reissueLicense()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('disableRenewal sends POST /auto-renewal/{orderId}/disable', async () => {
        await flushPromises()
        await wrapper.vm.disableRenewal()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/auto-renewal\/\d+\/disable/.test(r.url))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('asks before turning auto renewal off; cancelling sends nothing and resets the switch', async () => {
        await flushPromises()
        wrapper.vm.askRenewalOff(true) // turning on is not offered here
        expect(wrapper.vm.confirming).toBe(null)

        wrapper.vm.askRenewalOff(false)
        expect(wrapper.vm.confirming.title).toBe('message.order_renew_off_title')
        const key = wrapper.vm.switchKey
        wrapper.vm.cancelConfirm()
        expect(wrapper.vm.confirming).toBe(null)
        expect(wrapper.vm.switchKey).toBe(key + 1)
        expect(globalThis.mockHttp.history.post.some(r => /auto-renewal/.test(r.url))).toBe(false)

        wrapper.vm.askRenewalOff(false)
        await wrapper.vm.runConfirm()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/auto-renewal\/\d+\/disable/.test(r.url))).toBe(true)
    })

    it('asks before switching the license mode, with wording for each direction', async () => {
        await flushPromises()
        wrapper.vm.askLicenseMode(true)
        expect(wrapper.vm.confirming.label).toBe('message.order_file_on_confirm')
        wrapper.vm.askLicenseMode(false)
        expect(wrapper.vm.confirming.label).toBe('message.order_file_off_confirm')
        await wrapper.vm.runConfirm()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/switch-license-mode/.test(r.url))).toBe(true)
    })

    it('an empty activation code shows an error instead of a disabled button', async () => {
        await flushPromises()
        wrapper.vm.bindingForm.activationCode = '  '
        await wrapper.vm.submitBinding()
        expect(wrapper.vm.bindingError).toBe('message.order_activation_code_required')
        expect(globalThis.mockHttp.history.post.some(r => /license-binding/.test(r.url))).toBe(false)
    })

    it('arrow keys move between tabs', async () => {
        await flushPromises()
        const e = { key: 'ArrowRight', preventDefault: jest.fn() }
        wrapper.vm.onTabKeys(e, ['billing', 'features'], 'billing', wrapper.vm.selectTab, 'order-tab-')
        expect(wrapper.vm.tab).toBe('features')
        wrapper.vm.onTabKeys({ ...e, key: 'ArrowRight' }, ['billing', 'features'], 'features', wrapper.vm.selectTab, 'order-tab-')
        expect(wrapper.vm.tab).toBe('billing') // wraps around
        wrapper.vm.onTabKeys({ key: 'a', preventDefault: e.preventDefault }, ['billing', 'features'], 'billing', wrapper.vm.selectTab, 'order-tab-')
        expect(e.preventDefault).toHaveBeenCalledTimes(2)
    })

    it('toggleLicenseMode sends POST /switch-license-mode', async () => {
        await flushPromises()
        await wrapper.vm.toggleLicenseMode(true)
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/switch-license-mode/.test(r.url))).toBe(true)
    })

    it('saveLicenseEdit sends POST /update-license-details', async () => {
        await flushPromises()
        await wrapper.vm.saveLicenseEdit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/update-license-details/.test(r.url))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('does not POST update-license-details when validateForm returns false', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        globalThis.mockHttp.reset()
        await wrapper.vm.saveLicenseEdit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('openLicenseEditModal sets licenseEditModal.show to true', async () => {
        await flushPromises()
        wrapper.vm.openLicenseEditModal()
        expect(wrapper.vm.licenseEditModal.show).toBe(true)
    })

    it('opens on the Billing tab, with the license and its installs above the tabs', async () => {
        await flushPromises()
        wrapper.vm.installs = [
            { id: 1, domain: 'support.acme.com', ip: '10.0.0.1', version: 'v9.2.1', active: true, last_active: '2026-11-20' },
            { id: 2, domain: null, ip: '10.0.0.2', version: null, active: false, last_active: null },
        ]
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tab).toBe('billing')
        expect(wrapper.vm.tabs.map(t => t.key)).toEqual(['billing', 'features'])
        const rows = wrapper.findAll('.table-striped.mb-0 tbody tr')
        expect(rows.map(r => r.find('td').text())).toEqual(['support.acme.com 10.0.0.1', '10.0.0.2'])
        expect(rows[0].find('.badge').classes()).toContain('bg-success')
        expect(rows[1].find('.badge').classes()).toContain('bg-secondary')
    })

    it('shows current installs first; the history tab loads its table on first open', async () => {
        await flushPromises()
        expect(wrapper.vm.installTab).toBe('current')
        expect(wrapper.vm.historySeen).toBe(false)
        wrapper.vm.selectInstallTab('history')
        expect(wrapper.vm.installTab).toBe('history')
        expect(wrapper.vm.historySeen).toBe(true)
        expect(mockReplace).toHaveBeenLastCalledWith({ query: { installs: 'history' } })
    })

    it('unbinds one install after confirming, then reloads the order', async () => {
        await flushPromises()
        wrapper.vm.installs = [{ id: 7, domain: 'a.test', ip: '10.0.0.1', active: true }]
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.table-striped.mb-0 tbody tr action-button-stub').attributes('icon')).toBe('fas fa-link-slash')
        wrapper.vm.unbinding = wrapper.vm.installs[0] // what the row's Unbind button does

        const reloads = globalThis.mockHttp.history.get.length
        await wrapper.vm.onUnbound()
        expect(wrapper.vm.unbinding).toBe(null)
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(reloads)
    })

    it('masks the key but its last group, and flags a full install limit', async () => {
        await flushPromises()
        wrapper.vm.licenseDetails = { ...wrapper.vm.licenseDetails, licence_code: 'AAAABBBBCCCC0025', installation_limit: 2 }
        wrapper.vm.installs = [{ domain: 'a.test', active: true }, { domain: 'b.test', active: false }]
        wrapper.vm.keyHidden = true
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.keyText).toBe('•••• •••• •••• 0025')
        expect(wrapper.vm.limitReached).toBe(true)

        wrapper.vm.licenseDetails = { ...wrapper.vm.licenseDetails, installation_limit: 0 } // unlimited
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.limitReached).toBe(false)
    })

    it('puts the license end on the header bar and the other dates with the features', async () => {
        await flushPromises()
        const ymd = (days) => new Date(Date.now() + days * 86400000).toISOString().slice(0, 10)
        wrapper.vm.order.created_at = ymd(-30)
        wrapper.vm.rights.dates = [
            { code: 'license', label: 'License', ends_at: ymd(30) },
            { code: 'updates', label: 'Updates', ends_at: ymd(20) },
            { code: 'support', label: 'Support', ends_at: ymd(-3) },
        ]
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.featureDates.map(d => [d.code, d.tone])).toEqual([['updates', 'warn'], ['support', 'bad']])
        expect(wrapper.vm.term.tone).toBe('good')
        wrapper.vm.openDate(wrapper.vm.term.date)
        expect(wrapper.vm.dating.code).toBe('license')

        wrapper.vm.rights.dates = [{ code: 'license', label: 'License', ends_at: null }]
        expect(wrapper.vm.term.endText).toBe('message.order_date_never')
    })

    it('shows the license key in groups of four', async () => {
        await flushPromises()
        expect(wrapper.vm.keyChunks).toEqual(['LIC-', 'ABC1', '23'])
    })

    it('shows the product with its plan in the summary', async () => {
        await flushPromises()
        wrapper.vm.order.subscription = { plan: { name: 'Gold' } }
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('Test Product')
        expect(wrapper.text()).toContain('message.plan: Gold')
    })

    it('keeps the open tab in the URL and mounts its table on first visit', async () => {
        await flushPromises()
        expect(wrapper.vm.seen.billing).toBe(true)
        wrapper.vm.selectTab('features')
        expect(wrapper.vm.tab).toBe('features')
        expect(mockReplace).toHaveBeenLastCalledWith({ query: { tab: 'features' } })
        wrapper.vm.selectTab('billing')
        expect(mockReplace).toHaveBeenLastCalledWith({ query: { tab: undefined } })
    })

    it('marks a date that ended, one ending soon, and none for never', () => {
        const ymd = (days) => new Date(Date.now() + days * 86400000).toISOString().slice(0, 10)
        expect(wrapper.vm.dateHint({ ends_at: ymd(-2) }).text).toBe('message.order_date_ended')
        expect(wrapper.vm.dateHint({ ends_at: ymd(10) }).class).toContain('warning')
        expect(wrapper.vm.dateHint({ ends_at: null })).toBeNull()
    })

    it('saves a date, or never', async () => {
        await flushPromises()
        globalThis.mockHttp.onPut('/order/0/dates/support').reply(200, { message: 'ok' })
        wrapper.vm.openDate({ code: 'support', label: 'Support', ends_at: '2027-11-20' })
        wrapper.vm.dating.date = '2028-01-31'
        await wrapper.vm.saveDate()
        expect(JSON.parse(globalThis.mockHttp.history.put.at(-1).data)).toEqual({ date: '2028-01-31' })

        wrapper.vm.openDate({ code: 'support', label: 'Support', ends_at: '2028-01-31' })
        wrapper.vm.dating.never = true
        await wrapper.vm.saveDate()
        expect(JSON.parse(globalThis.mockHttp.history.put.at(-1).data)).toEqual({ date: null })
    })

    it('saveLicenseEdit sends only the installation fields (dates moved to Features & dates)', async () => {
        await flushPromises()
        wrapper.vm.openLicenseEditModal()
        await wrapper.vm.saveLicenseEdit()
        await flushPromises()
        const call = globalThis.mockHttp.history.post.filter(r => /\/update-license-details/.test(r.url)).at(-1)
        const body = JSON.parse(call.data)
        expect(body).toHaveProperty('limit')
        // Agents are changed like any feature, from Features & dates.
        expect(body).not.toHaveProperty('agents')
        expect(body).not.toHaveProperty('update_end')
        expect(body).not.toHaveProperty('subscription_end')
        expect(body).not.toHaveProperty('support_end')
    })

})
