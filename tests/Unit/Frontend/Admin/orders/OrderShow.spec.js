jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
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
    autorenewal: 0,
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

    it('tab starts as installations', () => {
        expect(wrapper.vm.tab).toBe('installations')
    })
})
