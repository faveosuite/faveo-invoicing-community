jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))

import { mount } from '@vue/test-utils'
import OrderEntitlements from '@/pages/admin/orders/components/OrderEntitlements.vue'

const row = (over) => ({ category: null, type: 'boolean', plan_value: true, extra: 0, override: null, until: null, value: true, locked: false, from_code: false, ...over })
const items = [
    row({ id: 1, code: 'sla', label: 'SLA' }),
    row({ id: 2, code: 'chat', label: 'Chat', plan_value: false, extra: 1 }),
    row({ id: 3, code: 'storage', label: 'Storage', type: 'number', plan_value: null, extra: 2, value: 2 }),
    row({ id: 4, code: 'sso', label: 'SSO', override: '1', until: '2027-01-31' }),
    ...Array.from({ length: 9 }, (_, i) => row({ id: 10 + i, code: `plugin_${i}`, label: `Plugin ${i}`, category: 'Plugins' })),
]

function make() {
    return mount(OrderEntitlements, {
        props: { orderId: 7, items, available: [{ id: 99, code: 'telephony', category: 'Channels', label: 'Telephony', type: 'boolean', options: null }] },
        global: { stubs: ['AppModal', 'AppAlert', 'DeleteModal', 'action-button'] },
    })
}

describe('OrderEntitlements.vue', () => {
    it('groups features by category; long groups after the first start collapsed, a search opens them', () => {
        const wrapper = make()
        expect(wrapper.vm.groups.map(g => [g.name, g.rows.length])).toEqual([['message.order_features', 4], ['Plugins', 9]])
        expect(wrapper.vm.isOpen(wrapper.vm.groups[0], 0)).toBe(true)
        expect(wrapper.vm.isOpen(wrapper.vm.groups[1], 1)).toBe(false)

        wrapper.vm.toggle(wrapper.vm.groups[1], 1)
        expect(wrapper.vm.isOpen(wrapper.vm.groups[1], 1)).toBe(true)
    })

    it('a search matches name or code and opens every group', async () => {
        const wrapper = make()
        await wrapper.find('#feature-search').setValue('plugin_3')
        expect(wrapper.vm.groups.map(g => g.rows.map(r => r.code))).toEqual([['plugin_3']])
        expect(wrapper.vm.isOpen(wrapper.vm.groups[0], 1)).toBe(true)
    })

    it('says where each value comes from', () => {
        const wrapper = make()
        const texts = (code) => wrapper.vm.sources(items.find(i => i.code === code)).map(p => p.text)
        expect(texts('sla')).toEqual(['message.entitlement_source_plan'])
        expect(texts('chat')).toEqual(['message.entitlement_source_bought'])
        expect(texts('storage')).toEqual(['message.entitlement_source_bought_units'])
        expect(texts('sso')).toEqual(['message.entitlement_source_override_until'])
    })

    it('"Give a feature" opens the editor switched on, and a save tells the page to reload', async () => {
        const wrapper = make()
        wrapper.vm.give('99')
        expect(wrapper.vm.editing).toMatchObject({ id: 99, value: '1', plan_value: null })

        globalThis.mockHttp.onPut('/order/7/entitlements/99').reply(200, { message: 'ok' })
        await wrapper.vm.save()
        expect(wrapper.emitted('changed')).toHaveLength(1)
    })
})
