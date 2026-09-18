jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CartItemRow from '@/themes/porto/components/cart/CartItemRow.vue'

const baseItem = {
    id: 1,
    name: 'Product A',
    image: null,
    unit_price: '99.00',
    line_total: '99.00',
    currency_symbol: '$',
    quantity: 2,
    agents: 5,
    can_modify_quantity: true,
    can_modify_agent: true,
    show_agent: true,
}

function makeWrapper(itemOverrides = {}) {
    return mount(CartItemRow, {
        props: { item: { ...baseItem, ...itemOverrides } },
        global: { plugins: [createTestingPinia()] },
    })
}

describe('CartItemRow.vue', () => {
    it('is a vue instance', () => {
        expect(makeWrapper().exists()).toBeTruthy()
    })

    it('renders the product name', () => {
        expect(makeWrapper().text()).toContain('Product A')
    })

    it('renders the unit price and currency symbol', () => {
        expect(makeWrapper().text()).toContain('$99.00')
    })

    // ── Thumbnail branches ───────────────────────────────────────────
    it('renders an img when item.image is set', () => {
        const w = makeWrapper({ image: 'http://example.com/img.png' })
        expect(w.find('img').exists()).toBe(true)
    })

    it('renders a placeholder span when item.image is null', () => {
        const w = makeWrapper({ image: null })
        expect(w.find('img').exists()).toBe(false)
        expect(w.find('.fa-box').exists()).toBe(true)
    })

    // ── Quantity branches ────────────────────────────────────────────
    it('renders quantity controls when can_modify_quantity is true', () => {
        const w = makeWrapper({ can_modify_quantity: true })
        expect(w.find('.quantity').exists()).toBe(true)
    })

    it('renders plain quantity text when can_modify_quantity is false', () => {
        // Override agents too so .quantity is absent entirely
        const w = makeWrapper({ can_modify_quantity: false, show_agent: false })
        expect(w.find('.quantity').exists()).toBe(false)
        expect(w.text()).toContain('2')
    })

    it('minus button is disabled when quantity is 1', () => {
        const w = makeWrapper({ quantity: 1 })
        const minus = w.find('input[value="-"]')
        expect(minus.element.disabled).toBe(true)
    })

    it('minus button is enabled when quantity > 1', () => {
        const w = makeWrapper({ quantity: 3 })
        const minus = w.find('input[value="-"]')
        expect(minus.element.disabled).toBe(false)
    })

    // ── Agents branches ──────────────────────────────────────────────
    it('renders — when show_agent is false', () => {
        const w = makeWrapper({ show_agent: false })
        const cells = w.findAll('td')
        expect(cells[4].text()).toContain('—')
    })

    it('renders "unlimited_agents" when show_agent is true and agents is 0', () => {
        const w = makeWrapper({ show_agent: true, agents: 0 })
        expect(w.text()).toContain('message.unlimited_agents')
    })

    it('renders agent controls when can_modify_agent is true and agents > 0', () => {
        const w = makeWrapper({ show_agent: true, agents: 5, can_modify_agent: true })
        const quantities = w.findAll('.quantity')
        expect(quantities.length).toBeGreaterThan(0)
    })

    it('renders plain agent count when can_modify_agent is false and agents > 0', () => {
        const w = makeWrapper({ show_agent: true, agents: 3, can_modify_agent: false })
        expect(w.text()).toContain('3')
    })

    // ── step() function ──────────────────────────────────────────────
    it('step() emits update with incremented quantity', async () => {
        const w = makeWrapper({ quantity: 2 })
        await w.find('input[value="+"]').trigger('click')
        const emitted = w.emitted('update')
        expect(emitted).toBeTruthy()
        expect(emitted[0][0]).toEqual({ id: 1, quantity: 3 })
    })

    it('step() emits update with decremented quantity', async () => {
        const w = makeWrapper({ quantity: 3 })
        await w.find('input[value="-"]').trigger('click')
        const emitted = w.emitted('update')
        expect(emitted).toBeTruthy()
        expect(emitted[0][0]).toEqual({ id: 1, quantity: 2 })
    })

    it('step() does not emit when quantity would go below 1', async () => {
        const w = makeWrapper({ quantity: 1 })
        // Direct call since button is disabled in DOM
        w.vm.step('quantity', -1)
        expect(w.emitted('update')).toBeFalsy()
    })

    it('remove button emits "remove" with item.id', async () => {
        const w = makeWrapper()
        await w.find('a.product-thumbnail-remove').trigger('click')
        expect(w.emitted('remove')).toBeTruthy()
        expect(w.emitted('remove')[0][0]).toBe(1)
    })
})
