jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CartCustomizeModal from '@/themes/porto/components/cart/CartCustomizeModal.vue'
import { useAuthStore } from '@/core/stores/auth'

const Modal = { props: ['showModal'], template: '<div v-if="showModal"><slot name="title" /><slot name="fields" /><slot name="controls" /></div>' }

const item = {
    id: 9, name: 'Helpdesk', period: '1 Year', currency_symbol: '$', line_total: '120.00', quantity: 1,
    can_modify_quantity: false,
    extras: [
        { entitlement_id: 3, label: 'Storage', type: 'number', quantity: 2, included: 10, unit_price: '2.00' },
        { entitlement_id: 4, label: 'Chat', type: 'boolean', quantity: 0, included: null, unit_price: '10.00' },
    ],
}

function make() {
    const wrapper = mount(CartCustomizeModal, {
        props: { item },
        global: { plugins: [createTestingPinia()], stubs: { Modal, 'router-link': true } },
    })
    useAuthStore().isAuthenticated = true
    return wrapper
}

describe('CartCustomizeModal.vue', () => {
    it('starts a limit the plan includes at the plan amount, and never below it', async () => {
        const wrapper = make()
        await wrapper.vm.$nextTick()
        const storage = wrapper.findAll('.co-stepper').find(s => s.find('input').attributes('aria-label') === 'Storage')
        expect(storage.find('input').element.value).toBe('12') // 10 included + 2 extra
        expect(wrapper.text()).toContain('message.cart_agents_included')

        await storage.findAll('button')[1].trigger('click') // +
        expect(wrapper.emitted('update').at(-1)[0]).toEqual({ id: 9, extras: { 3: 3 } }) // stores the 3 extra

        await storage.findAll('button')[0].trigger('click') // −
        expect(wrapper.emitted('update').at(-1)[0]).toEqual({ id: 9, extras: { 3: 1 } })
    })

    it('keeps the minus button off at the included amount', async () => {
        const wrapper = mount(CartCustomizeModal, {
            props: { item: { ...item, extras: [{ ...item.extras[0], quantity: 0 }] } },
            global: { plugins: [createTestingPinia()], stubs: { Modal, 'router-link': true } },
        })
        useAuthStore().isAuthenticated = true
        await wrapper.vm.$nextTick()
        const storage = wrapper.findAll('.co-stepper').find(s => s.find('input').attributes('aria-label') === 'Storage')
        expect(storage.find('input').element.value).toBe('10')
        expect(storage.findAll('button')[0].attributes('disabled')).toBeDefined()
    })

    it('sells agents as an extra; a plan that includes 0 keeps at least one', async () => {
        const agents = { entitlement_id: 1, label: 'Agents', type: 'number', quantity: 1, included: 0, min: 1, unit_price: '30.00' }
        const wrapper = mount(CartCustomizeModal, {
            props: { item: { ...item, extras: [agents] } },
            global: { plugins: [createTestingPinia()], stubs: { Modal, 'router-link': true } },
        })
        useAuthStore().isAuthenticated = true
        await wrapper.vm.$nextTick()
        const steppers = wrapper.findAll('.co-stepper').filter(s => s.find('input').attributes('aria-label') === 'Agents')
        expect(steppers).toHaveLength(1) // the extra's stepper only, no separate agents stepper
        expect(steppers[0].find('input').element.value).toBe('1')
        expect(steppers[0].findAll('button')[0].attributes('disabled')).toBeDefined()

        await steppers[0].findAll('button')[1].trigger('click') // +
        expect(wrapper.emitted('update').at(-1)[0]).toEqual({ id: 9, extras: { 1: 2 } })
    })

    it('lists features the plan does not include as add-ons, apart from the included limits', async () => {
        const wrapper = make()
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.topUps.map(e => e.label)).toEqual(['Storage'])
        expect(wrapper.vm.addons.map(e => e.label)).toEqual(['Chat'])
        expect(wrapper.find('#extra-9-4').exists()).toBe(true) // Chat keeps its checkbox
        expect(wrapper.find('#extra-9-3').exists()).toBe(false) // Storage is a stepper, not a tick
    })
})
