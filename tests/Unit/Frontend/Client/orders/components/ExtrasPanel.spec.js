jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }) }))

import { mount } from '@vue/test-utils'
import ExtrasPanel from '@/pages/client/orders/components/ExtrasPanel.vue'

const EXTRAS = [
    { id: 3, label: 'Storage', type: 'number', quantity: 2, renew_quantity: 2, unit_price: 10, currency: 'INR', can_buy: false, reason: "This isn't for sale." },
    { id: 13, label: 'Custom Forms', type: 'boolean', quantity: 0, renew_quantity: 0, unit_price: 12, currency: 'INR', can_buy: true, reason: null },
    { id: 1, label: 'Agents', type: 'number', quantity: 0, renew_quantity: 0, unit_price: 1, currency: 'INR', can_buy: false, reason: 'This order already has unlimited agents.' },
]

async function make() {
    globalThis.mockHttp.onGet('/my-order/7/extras').reply(200, { data: { extras: EXTRAS } })
    const wrapper = mount(ExtrasPanel, {
        props: { orderId: 7 },
        global: { stubs: ['AppModal', 'Alert', 'loader', 'action-button', 'DynamicSelect', 'TextField'] },
    })
    await flushPromises()
    return wrapper
}

describe('ExtrasPanel.vue', () => {
    afterEach(() => globalThis.mockHttp.reset())

    it('offers only what can be bought, and says why more of an owned one can\'t be', async () => {
        const wrapper = await make()
        expect(wrapper.vm.available.map(e => e.label)).toEqual(['Custom Forms']) // not Agents
        expect(wrapper.text()).toContain("This isn't for sale.")
        expect(wrapper.find('button').text()).toContain('Storage') // buttons name their add-on
    })

    it('checks the renewing quantity before saving', async () => {
        const wrapper = await make()
        wrapper.vm.openRenewal(EXTRAS[0])
        wrapper.vm.renewQty = 5 // more than the 2 owned
        await wrapper.vm.saveRenewal()
        expect(wrapper.vm.renewError).toBe('message.extra_reduce_range')
        expect(globalThis.mockHttp.history.post).toHaveLength(0)
    })
})
