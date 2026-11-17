jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CartItemRow from '@/themes/porto/components/cart/CartItemRow.vue'

const baseItem = {
    id: 1,
    name: 'Product A',
    image: null,
    unit_price: '99.00',
    plan_price: '99.00',
    plan_total: '198.00',
    line_total: '198.00',
    currency_symbol: '$',
    quantity: 2,
    agents: 5,
    included_agents: 5,
    agent_price: '10.00',
    agents_total: null,
    agents_fewer: false,
    period: '1 Year',
    can_modify_quantity: true,
    can_modify_agent: true,
    show_agent: true,
    extras: [],
}

// Table rows need a table around them to mount.
const makeWrapper = (overrides = {}, props = { showQuantity: true }) => mount({
    components: { CartItemRow },
    props: ['item', 'extra'],
    template: '<table><tbody><CartItemRow :item="item" v-bind="extra" @update="(e) => $emit(\'update\', e)" @remove="(e) => $emit(\'remove\', e)" /></tbody></table>',
}, {
    props: { item: { ...baseItem, ...overrides }, extra: props },
    global: { plugins: [createTestingPinia()] },
})

const steppers = w => w.findAllComponents({ name: 'CartQtyStepper' })
const row = w => w.findComponent(CartItemRow)

describe('CartItemRow.vue', () => {
    it('shows the product, its period and the plan price', () => {
        const w = makeWrapper()
        expect(w.text()).toContain('Product A')
        expect(w.text()).toContain('1 Year')
        expect(w.find('.product-price').text()).toBe('$99.00')
        expect(w.find('.product-subtotal').text()).toBe('$198.00')
    })

    it('renders a placeholder when there is no image', () => {
        expect(makeWrapper().find('.fa-box').exists()).toBe(true)
        expect(makeWrapper({ image: 'http://example.com/a.png' }).find('img').exists()).toBe(true)
    })

    it('has a quantity stepper only when the product allows it', () => {
        expect(steppers(makeWrapper({ show_agent: false })).length).toBe(1)
        expect(steppers(makeWrapper({ can_modify_quantity: false, show_agent: false })).length).toBe(0)
    })

    it('hides the quantity column when the cart has no steppers', () => {
        const w = makeWrapper({ can_modify_quantity: false, show_agent: false }, { showQuantity: false })
        expect(w.find('td.product-quantity').exists()).toBe(false)
    })

    it('has an agents row with the extra cost when the customer can choose agents', () => {
        const w = makeWrapper({ can_modify_quantity: false, agents: 7, agents_total: '20.00' })
        expect(steppers(w).length).toBe(1)
        expect(w.text()).toContain('+$20.00')
        expect(w.text()).toContain('message.cart_agents_included')
    })

    it('shows fewer agents than included as a reduction', () => {
        expect(makeWrapper({ agents: 3, agents_total: '20.00', agents_fewer: true }).text()).toContain('−$20.00')
    })

    it('says fixed or unlimited agents under the name instead', () => {
        expect(makeWrapper({ can_modify_agent: false, agents: 3 }).text()).toContain('message.agents_count')
        expect(makeWrapper({ agents: 0 }).text()).toContain('message.unlimited_agents')
        expect(makeWrapper({ show_agent: false }).text()).not.toContain('message.agents')
    })

    it('emits quantity changes, never below 1', () => {
        const w = makeWrapper({ show_agent: false })
        steppers(w)[0].vm.$emit('change', 3)
        expect(row(w).emitted('update')[0][0]).toEqual({ id: 1, quantity: 3 })
        row(w).vm.step('quantity', 0)
        expect(row(w).emitted('update').length).toBe(1)
    })

    it('lists chosen add-ons as rows and offers the rest', async () => {
        const extras = [
            { entitlement_id: 9, label: 'Storage', type: 'number', quantity: 2, unit_price: '1.00', line_total: '4.00' },
            { entitlement_id: 10, label: 'Chat', type: 'boolean', quantity: 0, unit_price: '5.00', line_total: '0.00' },
        ]
        const w = makeWrapper({ extras, show_agent: false })
        expect(w.findAll('tr').length).toBe(3) // plan, Storage, offers
        await w.find('button.btn-modern').trigger('click')
        expect(row(w).emitted('update')[0][0]).toEqual({ id: 1, extras: { 10: 1 } })
    })

    it('every row has the same number of cells as the header', () => {
        const extras = [{ entitlement_id: 9, label: 'Storage', type: 'number', quantity: 2, unit_price: '1.00', line_total: '4.00' }]
        const w = makeWrapper({ extras, agents: 7 })
        for (const tr of w.findAll('tr').slice(0, 3)) expect(tr.findAll('td').length).toBe(5)
    })

    it('remove button emits "remove" with the item id', async () => {
        const w = makeWrapper()
        await w.find('button.product-thumbnail-remove').trigger('click')
        expect(row(w).emitted('remove')[0][0]).toBe(1)
    })

    it('phone layout shows the same data as a card', () => {
        const w = mount(CartItemRow, { props: { item: { ...baseItem, agents: 7, agents_total: '20.00' }, layout: 'card' }, global: { plugins: [createTestingPinia()] } })
        expect(w.find('.card').exists()).toBe(true)
        expect(steppers(w).length).toBe(2) // quantity + agents
        expect(w.text()).toContain('$198.00')
    })
})
