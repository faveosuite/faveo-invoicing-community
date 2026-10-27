import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import Navbar from '@/themes/porto/components/common/Navbar.vue'
import { useCartStore } from '@/core/stores/cart'

jest.mock('@/themes/porto/components/store/BookDemoModal.vue', () => ({ template: '<div/>' }))

// Page-load count is 2; the live store must win once loaded — even when it's 0.
async function mountNavbar() {
    document.body.innerHTML = '<div id="app-client" data-cart-count="2"></div>'
    const router = createRouter({ history: createMemoryHistory(), routes: [{ path: '/', component: { template: '<div/>' } }] })
    await router.push('/')
    const pinia = createTestingPinia({ stubActions: true })
    const wrapper = mount(Navbar, { global: { plugins: [pinia, router] }, attachTo: document.body })
    await flushPromises()
    return { wrapper, cart: useCartStore(pinia) }
}

const badge = (w) => w.find('.cart-qty-badge').text()

describe('Navbar cart badge', () => {
    it('shows the page-load count before the cart store loads', async () => {
        const { wrapper } = await mountNavbar()
        expect(badge(wrapper)).toBe('2')
    })

    it('shows 0 after the last item is removed, not the stale page-load count', async () => {
        const { wrapper, cart } = await mountNavbar()
        cart.cart = { items: [], item_count: 0 }
        await flushPromises()
        expect(badge(wrapper)).toBe('0')
    })
})
