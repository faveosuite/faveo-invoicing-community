import { setActivePinia, createPinia } from 'pinia'
import { useCartStore } from '@/core/stores/cart.js'
import {
    emptyCartFixture,
    cartWithItemFixture,
    cartWithCouponFixture,
    checkoutCartFixture,
} from '../mocks/fixtures/index.js'

describe('useCartStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('initial state', () => {
        it('has cart as null', () => {
            const store = useCartStore()
            expect(store.cart).toBeNull()
        })

        it('has loading as false', () => {
            const store = useCartStore()
            expect(store.loading).toBe(false)
        })

        it('has error as null', () => {
            const store = useCartStore()
            expect(store.error).toBeNull()
        })
    })

    describe('getters (cart is null)', () => {
        it('items defaults to []', () => {
            const store = useCartStore()
            expect(store.items).toEqual([])
        })

        it('itemCount defaults to 0', () => {
            const store = useCartStore()
            expect(store.itemCount).toBe(0)
        })

        it('subtotal defaults to 0', () => {
            const store = useCartStore()
            expect(store.subtotal).toBe(0)
        })

        it('total defaults to 0', () => {
            const store = useCartStore()
            expect(store.total).toBe(0)
        })

        it('couponCode defaults to null', () => {
            const store = useCartStore()
            expect(store.couponCode).toBeNull()
        })

        it('couponDiscount defaults to 0', () => {
            const store = useCartStore()
            expect(store.couponDiscount).toBe(0)
        })

        it('currencySymbol defaults to $', () => {
            const store = useCartStore()
            expect(store.currencySymbol).toBe('$')
        })

        it('taxes defaults to []', () => {
            const store = useCartStore()
            expect(store.taxes).toEqual([])
        })

        it('taxTotal defaults to 0', () => {
            const store = useCartStore()
            expect(store.taxTotal).toBe(0)
        })

        it('subtotalExTax defaults to 0', () => {
            const store = useCartStore()
            expect(store.subtotalExTax).toBe(0)
        })

        it('pricesIncludeTax defaults to false', () => {
            const store = useCartStore()
            expect(store.pricesIncludeTax).toBe(false)
        })

        it('taxLabel defaults to empty string', () => {
            const store = useCartStore()
            expect(store.taxLabel).toBe('')
        })

        it('gateways defaults to []', () => {
            const store = useCartStore()
            expect(store.gateways).toEqual([])
        })

        it('grandTotal defaults to 0', () => {
            const store = useCartStore()
            expect(store.grandTotal).toBe(0)
        })
    })

    describe('getters (cart populated)', () => {
        it('returns items from cart', () => {
            const store = useCartStore()
            store.cart = cartWithItemFixture
            expect(store.items).toEqual(cartWithItemFixture.items)
        })

        it('returns itemCount from cart', () => {
            const store = useCartStore()
            store.cart = cartWithItemFixture
            expect(store.itemCount).toBe(1)
        })

        it('returns subtotal from cart', () => {
            const store = useCartStore()
            store.cart = cartWithItemFixture
            expect(store.subtotal).toBe(99)
        })

        it('returns couponCode from cart with coupon', () => {
            const store = useCartStore()
            store.cart = cartWithCouponFixture
            expect(store.couponCode).toBe('SAVE10')
        })

        it('returns couponDiscount from cart with coupon', () => {
            const store = useCartStore()
            store.cart = cartWithCouponFixture
            expect(store.couponDiscount).toBe(10)
        })

        it('returns gateways from checkout cart', () => {
            const store = useCartStore()
            store.cart = checkoutCartFixture
            expect(store.gateways).toHaveLength(2)
        })

        it('returns taxes from checkout cart', () => {
            const store = useCartStore()
            store.cart = checkoutCartFixture
            expect(store.taxes).toHaveLength(1)
        })

        it('returns grandTotal from checkout cart', () => {
            const store = useCartStore()
            store.cart = checkoutCartFixture
            expect(store.grandTotal).toBe(118.8)
        })

        it('subtotalExTax falls back to subtotal when field is absent', () => {
            const store = useCartStore()
            store.cart = { subtotal: 50 }
            expect(store.subtotalExTax).toBe(50)
        })
    })

    describe('fetchCart', () => {
        it('sets cart to emptyCartFixture on success', async () => {
            const store = useCartStore()
            await store.fetchCart()
            await flushPromises()
            expect(store.cart).toEqual(emptyCartFixture)
        })

        it('clears error on success', async () => {
            const store = useCartStore()
            store.error = 'previous error'
            await store.fetchCart()
            await flushPromises()
            expect(store.error).toBeNull()
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.fetchCart()
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 500', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/cart').replyOnce(500, { message: 'Server Error' })
            const store = useCartStore()
            await store.fetchCart()
            await flushPromises()
            expect(store.error).not.toBeNull()
        })

        it('sets loading to false after error', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/cart').replyOnce(500)
            const store = useCartStore()
            await store.fetchCart()
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('leaves cart unchanged on error', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/cart').replyOnce(500)
            const store = useCartStore()
            store.cart = cartWithItemFixture
            await store.fetchCart()
            await flushPromises()
            expect(store.cart).toEqual(cartWithItemFixture)
        })
    })

    describe('fetchCheckout', () => {
        it('sets cart to checkoutCartFixture on success', async () => {
            const store = useCartStore()
            await store.fetchCheckout()
            await flushPromises()
            expect(store.cart).toEqual(checkoutCartFixture)
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.fetchCheckout()
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 422', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/cart/checkout').replyOnce(422, { message: 'Unprocessable' })
            const store = useCartStore()
            await store.fetchCheckout()
            await flushPromises()
            expect(store.error).not.toBeNull()
        })

        it('sets loading to false after error', async () => {
            global.mockHttp.reset()
            global.mockHttp.onGet('/cart/checkout').replyOnce(500)
            const store = useCartStore()
            await store.fetchCheckout()
            await flushPromises()
            expect(store.loading).toBe(false)
        })
    })

    describe('addItem', () => {
        it('sets cart to cartWithItemFixture on success', async () => {
            const store = useCartStore()
            await store.addItem({ product_id: 10, plan_id: 1 })
            await flushPromises()
            expect(store.cart).toEqual(cartWithItemFixture)
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.addItem({ product_id: 10 })
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error and re-throws on 422', async () => {
            global.mockHttp.reset()
            global.mockHttp.onPost('/cart/items').replyOnce(422, { message: 'Invalid product' })
            const store = useCartStore()
            await expect(store.addItem({ product_id: 99 })).rejects.toBeDefined()
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })

        it('sets error and re-throws on 500', async () => {
            global.mockHttp.reset()
            global.mockHttp.onPost('/cart/items').replyOnce(500)
            const store = useCartStore()
            await expect(store.addItem({ product_id: 10 })).rejects.toBeDefined()
            await flushPromises()
            expect(store.error).not.toBeNull()
        })
    })

    describe('updateItem', () => {
        it('sets cart on success', async () => {
            const store = useCartStore()
            await store.updateItem(1, { qty: 2 })
            await flushPromises()
            expect(store.cart).toEqual(cartWithItemFixture)
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.updateItem(1, { qty: 2 })
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 422', async () => {
            global.mockHttp.reset()
            global.mockHttp.onPut('/cart/items/1').replyOnce(422, { message: 'Invalid qty' })
            const store = useCartStore()
            await store.updateItem(1, { qty: -1 })
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })
    })

    describe('removeItem', () => {
        it('sets cart to emptyCartFixture after removing the last item', async () => {
            const store = useCartStore()
            store.cart = cartWithItemFixture
            await store.removeItem(1)
            await flushPromises()
            expect(store.cart).toEqual(emptyCartFixture)
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.removeItem(1)
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 404', async () => {
            global.mockHttp.reset()
            global.mockHttp.onDelete('/cart/items/99').replyOnce(404, { message: 'Not found' })
            const store = useCartStore()
            await store.removeItem(99)
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })
    })

    describe('clearCart', () => {
        it('sets cart to emptyCartFixture on success', async () => {
            const store = useCartStore()
            store.cart = cartWithItemFixture
            await store.clearCart()
            await flushPromises()
            expect(store.cart).toEqual(emptyCartFixture)
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.clearCart()
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 500', async () => {
            global.mockHttp.reset()
            global.mockHttp.onDelete('/cart').replyOnce(500)
            const store = useCartStore()
            await store.clearCart()
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })
    })

    describe('applyCoupon', () => {
        it('sets cart to cartWithCouponFixture on success', async () => {
            const store = useCartStore()
            await store.applyCoupon('SAVE10')
            await flushPromises()
            expect(store.cart).toEqual(cartWithCouponFixture)
            expect(store.couponCode).toBe('SAVE10')
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.applyCoupon('SAVE10')
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error and re-throws on 422', async () => {
            global.mockHttp.reset()
            global.mockHttp.onPost('/cart/coupon').replyOnce(422, { message: 'Invalid coupon' })
            const store = useCartStore()
            await expect(store.applyCoupon('BADCODE')).rejects.toBeDefined()
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })
    })

    describe('removeCoupon', () => {
        it('sets cart to cartWithItemFixture (coupon removed) on success', async () => {
            const store = useCartStore()
            store.cart = cartWithCouponFixture
            await store.removeCoupon()
            await flushPromises()
            expect(store.cart).toEqual(cartWithItemFixture)
            expect(store.couponCode).toBeNull()
        })

        it('sets loading to false after success', async () => {
            const store = useCartStore()
            await store.removeCoupon()
            await flushPromises()
            expect(store.loading).toBe(false)
        })

        it('sets error on 500', async () => {
            global.mockHttp.reset()
            global.mockHttp.onDelete('/cart/coupon').replyOnce(500)
            const store = useCartStore()
            await store.removeCoupon()
            await flushPromises()
            expect(store.error).not.toBeNull()
            expect(store.loading).toBe(false)
        })
    })
})
