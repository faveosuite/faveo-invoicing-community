jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))

import { mount } from '@vue/test-utils'
import OrderEntitlements from '@/pages/admin/orders/components/OrderEntitlements.vue'

const page = {
    entitlements: [
        { id: 1, code: 'sla', category: null, label: 'SLA', type: 'boolean', plan_value: true, extra: 0, override: null, until: null, value: true, locked: false },
        { id: 2, code: 'wht_app', category: 'Plugins', label: 'WhatsApp', type: 'boolean', plan_value: false, extra: 1, override: null, until: null, value: true, locked: false },
    ],
    available: [{ id: 3, code: 'chat', category: null, label: 'Chat', type: 'boolean', options: null }],
    dates: [
        { code: 'license', label: 'License', ends_at: '2027-11-20' },
        { code: 'support', label: 'Support', ends_at: null },
    ],
    has_license: true,
}

describe('OrderEntitlements.vue', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet('/order/7/entitlements').reply(200, { data: page })
        wrapper = mount(OrderEntitlements, {
            props: { orderId: 7 },
            global: { stubs: ['v-client-table', 'AppModal', 'AppAlert', 'DeleteModal', 'loader', 'action-button'] },
        })
        await flushPromises()
    })

    it('groups what the order has by category, and offers the rest to give', () => {
        expect(wrapper.vm.groups.map(g => [g.name, g.rows.map(r => r.code)])).toEqual([
            ['message.entitlements', ['sla']],
            ['Plugins', ['wht_app']],
        ])
        expect(wrapper.vm.availableOptions).toEqual([{ id: '3', name: 'Chat' }])
    })

    it('"Give a feature" opens the editor switched on', () => {
        wrapper.vm.give('3')
        expect(wrapper.vm.editing).toMatchObject({ id: 3, value: '1', plan_value: null })
    })

    it('saves a date, or never', async () => {
        globalThis.mockHttp.onPut('/order/7/dates/support').reply(200, { message: 'ok' })

        wrapper.vm.openDate(page.dates[1])
        expect(wrapper.vm.dating.never).toBe(true)
        wrapper.vm.dating.never = false
        wrapper.vm.dating.date = '2027-06-30'
        await wrapper.vm.saveDate()

        const put = globalThis.mockHttp.history.put.find(r => r.url === '/order/7/dates/support')
        expect(JSON.parse(put.data)).toEqual({ date: '2027-06-30' })

        wrapper.vm.openDate(page.dates[0])
        wrapper.vm.dating.never = true
        globalThis.mockHttp.onPut('/order/7/dates/license').reply(200, { message: 'ok' })
        await wrapper.vm.saveDate()
        expect(JSON.parse(globalThis.mockHttp.history.put.at(-1).data)).toEqual({ date: null })
    })
})
