jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
const mockReplace = jest.fn()
const mockRoute = { query: {} }
jest.mock('vue-router', () => ({
    useRouter: () => ({ replace: mockReplace }),
    useRoute: () => mockRoute,
}))

import { mount } from '@vue/test-utils'
import OrderEntitlements from '@/pages/admin/orders/components/OrderEntitlements.vue'
import ActionButton from '@/components/Reusable/ActionButton.vue'

const row = (over) => ({ category: null, type: 'boolean', plan_value: true, in_plan: true, extra: 0, overridden: false, override: null, until: null, value: true, from_code: false, ...over })
const items = [
    row({ id: 0, code: 'agents', label: 'Agents', type: 'number', value: null, from_code: true }), // from the license code: 0000, unlimited
    row({ id: 1, code: 'sla', label: 'SLA' }),
    row({ id: 2, code: 'chat', label: 'Chat', plan_value: false, extra: 1 }),
    row({ id: 3, code: 'storage', label: 'Storage', type: 'number', plan_value: null, in_plan: false, extra: 2, value: 2 }),
    row({ id: 4, code: 'sso', label: 'SSO', overridden: true, override: '1', until: '2027-01-31' }),
    ...Array.from({ length: 9 }, (_, i) => row({ id: 10 + i, code: `plugin_${i}`, label: `Plugin ${i}`, category: 'Plugins' })),
]

function make() {
    return mount(OrderEntitlements, {
        props: { orderId: 7, items, available: [{ id: 99, code: 'telephony', category: 'Channels', label: 'Telephony', type: 'boolean', options: null }] },
        global: { stubs: ['AppModal', 'AppAlert', 'DeleteModal'], components: { 'action-button': ActionButton } },
    })
}

describe('OrderEntitlements.vue', () => {
    it('lists the date rows first, hands their edit to the page, and edits agents like any feature', async () => {
        const agents = row({ id: 0, code: 'agents', label: 'Agents', type: 'number', plan_value: 5, value: 25, from_code: true })
        const wrapper = mount(OrderEntitlements, {
            props: { orderId: 7, items: [agents], dates: [{ code: 'updates', label: 'Updates', ends_at: '2027-01-01', hint: 'in 20 days', tone: 'warn' }] },
            global: { stubs: ['AppModal', 'AppAlert', 'DeleteModal'], components: { 'action-button': ActionButton } },
        })
        const trs = wrapper.findAll('tbody tr')
        expect(trs.map(tr => tr.find('td').text())).toEqual(['Updates', 'Agents'])
        await trs[0].find('button').trigger('click')
        expect(wrapper.emitted('edit-date')[0][0].code).toBe('updates')
        await trs[1].find('button').trigger('click')
        expect(wrapper.vm.editing.code).toBe('agents')
    })

    it('a number is set to a value or to Unlimited, never left blank', async () => {
        const Modal = { template: '<div><slot name="fields" /></div>' }
        const agents = row({ id: 0, code: 'agents', label: 'Agents', type: 'number', plan_value: 5, value: 5, from_code: true })
        const storage = row({ id: 3, code: 'storage', label: 'Storage', type: 'number', plan_value: null, value: null }) // unlimited on the plan
        const wrapper = mount(OrderEntitlements, {
            props: { orderId: 7, items: [agents, storage] },
            global: { stubs: { AppModal: Modal, AppAlert: true, DeleteModal: true, DatePicker: true }, components: { 'action-button': ActionButton } },
        })
        expect(wrapper.findAll('tbody tr')[1].findAll('td')[2].text()).toBe('message.unlimited')

        await wrapper.findAll('tbody tr')[0].find('button').trigger('click')
        expect(wrapper.find('input[name="value_kind"][value="unlimited"]').exists()).toBe(true)
        expect(wrapper.vm.editing.unlimited).toBe(false)
        wrapper.vm.editing.value = ''
        await wrapper.vm.save()
        expect(globalThis.mockHttp.history.put).toHaveLength(0) // blank refused

        await wrapper.find('input[name="value_kind"][value="unlimited"]').setValue(true)
        globalThis.mockHttp.onPut('/order/7/entitlements/0').reply(200, { message: 'ok' })
        await wrapper.vm.save()
        expect(JSON.parse(globalThis.mockHttp.history.put[0].data)).toEqual({ unlimited: true, until: null })

        await wrapper.findAll('tbody tr')[1].find('button').trigger('click')
        expect(wrapper.vm.editing.unlimited).toBe(true) // the plan's unlimited
        globalThis.mockHttp.reset()
    })

    it('shows every feature in one table with its category, 10 a page', async () => {
        const wrapper = make()
        expect(wrapper.vm.rows).toHaveLength(14)
        expect(wrapper.findAll('tbody tr')).toHaveLength(10) // first page
        expect(wrapper.vm.pages).toBe(2)
        expect(wrapper.text()).toContain('message.datatable_info')

        wrapper.vm.page = 2
        await wrapper.vm.$nextTick()
        expect(wrapper.findAll('tbody tr')).toHaveLength(4)
        expect(wrapper.findAll('tbody tr').at(-1).findAll('td')[1].text()).toBe('Plugins')
    })

    it('a search matches name or code', async () => {
        const wrapper = make()
        wrapper.vm.page = 2
        await wrapper.find('input.globe-search').setValue('plugin_3')
        await wrapper.find('input.globe-search').trigger('keyup.enter')
        expect(wrapper.vm.rows.map(r => r.code)).toEqual(['plugin_3'])
        expect(wrapper.vm.page).toBe(1) // a new search starts at page 1
        await wrapper.find('input.globe-search').setValue('nothing-here')
        await wrapper.find('input.globe-search').trigger('keyup.enter')
        expect(wrapper.find('tbody').text()).toBe('message.entitlement_no_match')
    })

    it('starts from the category in the URL and writes filters back to it', async () => {
        mockRoute.query = { fcat: 'Plugins' }
        const wrapper = make()
        mockRoute.query = {}
        expect(wrapper.vm.category).toBe('Plugins')
        expect(wrapper.vm.rows).toHaveLength(9)
        wrapper.vm.category = ''
        await wrapper.vm.$nextTick()
        expect(mockReplace).toHaveBeenLastCalledWith({ query: { fcat: undefined, fq: undefined, fpage: undefined } })
    })

    it('a category button narrows the table to that category', async () => {
        const wrapper = make()
        expect(wrapper.vm.categories).toEqual(['message.order_features', 'Plugins'])
        wrapper.vm.page = 2
        await wrapper.findAll('[role="group"] button').at(2).trigger('click')
        expect(wrapper.vm.category).toBe('Plugins')
        expect(wrapper.vm.rows).toHaveLength(9)
        expect(wrapper.vm.page).toBe(1)
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
