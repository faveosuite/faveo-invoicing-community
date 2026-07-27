import { defineStore } from 'pinia'
import http, { parseErrorMessage } from '@/plugins/axios'

export const useCartStore = defineStore('cart', {
    state: () => ({
        cart: null,
        loading: false,
        error: null,
    }),
    getters: {
        items: (state) => state.cart?.items ?? [],
        itemCount: (state) => state.cart?.item_count ?? 0,
        subtotal: (state) => state.cart?.subtotal ?? 0,
        total: (state) => state.cart?.total ?? 0,
        couponCode: (state) => state.cart?.coupon_code ?? null,
        couponDiscount: (state) => state.cart?.coupon_discount ?? 0,
        currencySymbol: (state) => state.cart?.currency_symbol ?? '$',
        // Checkout-only fields (populated by fetchCheckout()).
        taxes: (state) => state.cart?.taxes ?? [],
        taxTotal: (state) => state.cart?.tax_total ?? 0,
        subtotalExTax: (state) => state.cart?.subtotal_ex_tax ?? state.cart?.subtotal ?? 0,
        pricesIncludeTax: (state) => state.cart?.prices_include_tax ?? false,
        taxLabel: (state) => state.cart?.tax_label ?? '',
        gateways: (state) => state.cart?.gateways ?? [],
        grandTotal: (state) => state.cart?.grand_total ?? 0,
        availableCredit: (state) => state.cart?.available_credit ?? 0,
    },
    actions: {
        async fetchCart() {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.get(`/my-cart`)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },

        // Cart + computed taxes + active payment gateways (checkout page).
        async fetchCheckout() {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.get(`/my-cart/checkout`)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },

        async addItem(payload) {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.post(`/my-cart/items`, payload)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
                throw e
            } finally {
                this.loading = false
            }
        },

        async updateItem(itemId, payload) {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.put(`/my-cart/items/${itemId}`, payload)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },

        async removeItem(itemId) {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.delete(`/my-cart/items/${itemId}`)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },

        async clearCart() {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.delete(`/my-cart`)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },

        async applyCoupon(code) {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.post(`/my-cart/coupon`, { code })
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
                throw e
            } finally {
                this.loading = false
            }
        },

        async removeCoupon() {
            this.loading = true
            this.error = null
            try {
                const { data } = await http.delete(`/my-cart/coupon`)
                this.cart = data.data
            } catch (e) {
                this.error = parseErrorMessage(e)
            } finally {
                this.loading = false
            }
        },
    },
})
