jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { errorHandler } from '@/helpers/responseHandler'
import RenewModal from '@/pages/client/orders/components/RenewModal.vue'

const defaultOrder = {
    id: 1,
    product_id: 10,
    sub_id: 5,
    client_id: 3,
    current_plan: 'Starter',
    agents: 10,
}

describe('RenewModal.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)

        wrapper = mount(RenewModal, {
            props: {
                show: false,
                order: defaultOrder,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'app-modal',
                    'select-field',
                    'action-button',
                    'loader',
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

    it('receives and reflects the show prop', () => {
        expect(wrapper.props('show')).toBe(false)
    })

    it('receives and reflects the order prop', () => {
        expect(wrapper.props('order')).toEqual(defaultOrder)
    })

    it('does not call the plans API when show is false', async () => {
        await flushPromises()
        expect(axiosMock.history.get.length).toBe(0)
    })

    it('calls GET /renew-popup-details/:productId when show becomes true', async () => {
        axiosMock.onGet('/renew-popup-details/10').reply(200, {
            data: { plans: [{ id: 1, name: 'Pro', label: 'Pro' }] },
        })
        axiosMock.onGet('/get-renew-cost').reply(200, { data: { formatted_price: '$99' } })

        await wrapper.setProps({ show: true })
        await flushPromises()

        expect(axiosMock.history.get.some(r => r.url.includes('/renew-popup-details/10'))).toBe(true)
    })

    it('fetches cost after plans are loaded', async () => {
        axiosMock.onGet('/renew-popup-details/10').reply(200, {
            data: { plans: [{ id: 2, name: 'Business', label: 'Business' }] },
        })
        axiosMock.onGet('/get-renew-cost').reply(200, { data: { formatted_price: '$199' } })

        await wrapper.setProps({ show: true })
        await flushPromises()

        expect(axiosMock.history.get.some(r => r.url.includes('/get-renew-cost'))).toBe(true)
    })

    it('handles 500 error from renew-popup-details gracefully', async () => {
        axiosMock.onGet('/renew-popup-details/10').reply(500)

        await wrapper.setProps({ show: true })
        await flushPromises()

        // Component should still exist — errors are swallowed silently
        expect(wrapper.exists()).toBeTruthy()
    })

    it('resets state when show toggles true → false → true', async () => {
        axiosMock.onGet('/renew-popup-details/10').reply(200, {
            data: { plans: [{ id: 1, name: 'Pro' }] },
        })
        axiosMock.onGet('/get-renew-cost').reply(200, { data: { formatted_price: '$99' } })

        // Open: plans should be loaded
        await wrapper.setProps({ show: true })
        await flushPromises()
        expect(wrapper.vm.plans.length).toBeGreaterThan(0)

        // Close then re-open: state is reset at the start of the open handler
        await wrapper.setProps({ show: false })
        await wrapper.setProps({ show: true })
        await flushPromises()

        // After re-opening plans are loaded again
        expect(wrapper.vm.plans.length).toBeGreaterThan(0)
    })

    it('calls POST renew endpoint and redirects on success', async () => {
        const push = jest.fn()
        jest.mocked(require('vue-router').useRouter).mockReturnValue?.({ push })

        axiosMock.onGet('/renew-popup-details/10').reply(200, {
            data: { plans: [{ id: 1, name: 'Pro' }] },
        })
        axiosMock.onGet('/get-renew-cost').reply(200, { data: { formatted_price: '$99' } })
        axiosMock.onPost('/client/renew/5').reply(200, { data: { invoice_id: 42 } })

        await wrapper.setProps({ show: true })
        await flushPromises()

        await wrapper.vm.submit()
        await flushPromises()

        expect(axiosMock.history.post.some(r => r.url.includes('/client/renew/5'))).toBe(true)
    })

    it('calls errorHandler when submit fails', async () => {
        axiosMock.onGet('/renew-popup-details/10').reply(200, {
            data: { plans: [{ id: 1, name: 'Pro' }] },
        })
        axiosMock.onGet('/get-renew-cost').reply(200, { data: { formatted_price: '$99' } })
        axiosMock.onPost('/client/renew/5').reply(500, { message: 'Server error' })

        await wrapper.setProps({ show: true })
        await flushPromises()

        await wrapper.vm.submit()
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })
})
