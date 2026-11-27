jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CartItemRow from '@/themes/porto/components/cart/CartItemRow.vue'

const baseItem = {
    id: 1,
    name: 'Product A',
    image: null,
    unit_price: '99.00',
    plan_total: '99.00',
    line_total: '99.00',
    extras_total: '0.00',
    currency_symbol: '$',
    quantity: 1,
    period: '1 Year',
    can_modify_quantity: true,
    extras: [],
}

const makeWrapper = (overrides = {}) => mount(CartItemRow, {
    props: { item: { ...baseItem, ...overrides } },
    global: { plugins: [createTestingPinia()] },
})

const extra = (id, overrides = {}) => ({ entitlement_id: id, label: `Extra ${id}`, type: 'boolean', quantity: 1, unit_price: '5.00', line_total: '5.00', ...overrides })

describe('CartItemRow.vue', () => {
    it('shows the product, its period and its price', () => {
        const w = makeWrapper()
        expect(w.find('.co-name').text()).toBe('Product A')
        expect(w.text()).toContain('1 Year')
        expect(w.find('.co-price').text()).toBe('$99.00')
    })

    it('shows a topped-up limit as the extra and the total, not just units', () => {
        const w = makeWrapper({ extras: [extra(3, { label: 'Storage', type: 'number', quantity: 5, included: 10, line_total: '10.00' })] })
        expect(w.find('.co-breakdown').text()).toContain('Storage (message.cart_extra_on_top)')
        expect(w.find('.co-breakdown').text()).not.toContain('message.cart_units')
    })

    it('renders a placeholder when there is no image', () => {
        expect(makeWrapper().find('.fa-box').exists()).toBe(true)
        expect(makeWrapper({ image: 'http://example.com/a.png' }).find('img').exists()).toBe(true)
    })

    it('has no agents chip: agents show as an extra line like any other', () => {
        expect(makeWrapper().text()).not.toContain('agent')
    })

    it('shows no breakdown when the price is just the plan', () => {
        expect(makeWrapper().find('.co-breakdown').exists()).toBe(false)
    })

    it('lists extra agents as an Agents extra, with no fewer-agents row', () => {
        const w = makeWrapper({ extras: [extra(1, { label: 'Agents', type: 'number', quantity: 2, included: 5, line_total: '20.00' })] })
        expect(w.find('.co-breakdown').text()).toContain('Agents (message.cart_extra_on_top)')
        expect(w.find('.co-breakdown').text()).toContain('+$20.00')
        expect(w.find('.co-row.is-discount').exists()).toBe(false)
        expect(w.text()).not.toContain('message.cart_fewer_agents')
    })

    it('lists each chosen add-on, and folds more than 3 into one line that opens up', async () => {
        const few = makeWrapper({ extras: [extra(1), extra(2, { quantity: 0 })] })
        expect(few.text()).toContain('Extra 1')
        expect(few.text()).not.toContain('Extra 2')

        const many = makeWrapper({ extras: [1, 2, 3, 4].map(i => extra(i)), extras_total: '20.00' })
        expect(many.text()).toContain('message.cart_addons_count')
        expect(many.text()).not.toContain('Extra 1')
        await many.find('[aria-expanded]').trigger('click')
        expect(many.text()).toContain('Extra 4')
    })

    it('shows the price per unit when there are several and nothing else to break down', () => {
        expect(makeWrapper({ quantity: 2 }).text()).toContain('message.cart_each')
    })

    it('offers Customize only when something can change, and emits the item id', async () => {
        const fixed = makeWrapper({ can_modify_quantity: false })
        expect(fixed.text()).not.toContain('message.cart_customize')

        const w = makeWrapper()
        await w.findAll('button').find(b => b.text().includes('message.cart_customize')).trigger('click')
        expect(w.emitted('customize')[0][0]).toBe(1)
    })

    it('remove button emits "remove" with the item id', async () => {
        const w = makeWrapper()
        await w.find('.co-link-danger').trigger('click')
        expect(w.emitted('remove')[0][0]).toBe(1)
    })
})
