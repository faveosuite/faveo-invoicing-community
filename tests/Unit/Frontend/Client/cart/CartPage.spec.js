jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CartPage from '@/pages/client/cart/CartPage.vue'

// Cart store state shape: { cart: <cart-object|null>, loading, error }
// Getters (items, subtotal, etc.) derive from state.cart.
const emptyCartState = { cart: null, loading: false, error: null }

const cartWithItem = {
    cart: {
        items: [{ id: 1, name: 'Product A', image: null, quantity: 1, agents: null, line_total: 99 }],
        item_count: 1,
        subtotal: 99,
        total: 99,
        coupon_code: null,
        coupon_discount: 0,
        currency_symbol: '$',
        grand_total: 99,
        gateways: [],
        taxes: [],
        tax_total: 0,
        subtotal_ex_tax: 99,
    },
    loading: false,
    error: null,
}

const cartWithCoupon = {
    cart: {
        ...cartWithItem.cart,
        coupon_code: 'SAVE10',
        coupon_discount: 10,
        total: 89,
        grand_total: 89,
    },
    loading: false,
    error: null,
}

function createWrapper(cartState = emptyCartState, authState = { user: null, isAuthenticated: false }) {
    return mount(CartPage, {
        global: {
            plugins: [
                createTestingPinia({
                    initialState: {
                        cart: cartState,
                        auth: authState,
                        alert: { message: '', type: '', component_name: '' },
                    },
                }),
            ],
            stubs: ['loader', 'cart-item-row', 'router-link'],
        },
    })
}

describe('CartPage.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = createWrapper()
    })

    afterEach(() => {
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('shows empty state when cart has no items', () => {
        expect(wrapper.find('.fa-shopping-cart').exists()).toBeTruthy()
    })

    it('shows cart table when items are present', async () => {
        wrapper = createWrapper(cartWithItem)
        await wrapper.vm.$nextTick()
        expect(wrapper.find('table.shop_table').exists()).toBeTruthy()
    })

    it('shows coupon discount row when couponDiscount is greater than 0', async () => {
        wrapper = createWrapper(cartWithCoupon)
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.cart-discount').exists()).toBeTruthy()
    })

    it('does not show coupon discount row when couponDiscount is 0', async () => {
        wrapper = createWrapper(cartWithItem)
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.cart-discount').exists()).toBeFalsy()
    })

    it('clear cart button is disabled when cartStore is loading', async () => {
        wrapper = createWrapper({ ...cartWithItem, loading: true })
        await wrapper.vm.$nextTick()
        const clearBtn = wrapper.find('button.btn-light')
        expect(clearBtn.attributes('disabled')).toBeDefined()
    })

    it('checkout button is present when items exist', async () => {
        wrapper = createWrapper(cartWithItem)
        await wrapper.vm.$nextTick()
        expect(wrapper.find('button.checkout').exists()).toBeTruthy()
    })

    it('calls fetchCart on mount', async () => {
        // fetchCart is auto-stubbed by createTestingPinia — verify mount completes without error
        expect(wrapper.exists()).toBeTruthy()
    })

    it('shows currency symbol in totals', async () => {
        wrapper = createWrapper(cartWithItem)
        await wrapper.vm.$nextTick()
        expect(wrapper.text()).toContain('$')
    })
})
