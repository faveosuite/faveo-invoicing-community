export const emptyCartFixture = {
    items: [],
    item_count: 0,
    subtotal: 0,
    total: 0,
    coupon_code: null,
    coupon_discount: 0,
    currency_symbol: '$',
    taxes: [],
    tax_total: 0,
    subtotal_ex_tax: 0,
    prices_include_tax: false,
    tax_label: '',
    gateways: [],
    grand_total: 0,
}

export const cartItemFixture = {
    id: 1,
    product_id: 10,
    name: 'Product A',
    plan_name: 'Monthly',
    price: 99,
    qty: 1,
    subtotal: 99,
}

export const cartWithItemFixture = {
    ...emptyCartFixture,
    items: [cartItemFixture],
    item_count: 1,
    subtotal: 99,
    total: 99,
    grand_total: 99,
}

export const cartWithCouponFixture = {
    ...cartWithItemFixture,
    coupon_code: 'SAVE10',
    coupon_discount: 10,
    total: 89,
    grand_total: 89,
}

export const checkoutCartFixture = {
    ...cartWithItemFixture,
    gateways: [
        { id: 1, name: 'Stripe', slug: 'stripe', logo: null },
        { id: 2, name: 'PayPal', slug: 'paypal', logo: null },
    ],
    taxes: [{ name: 'VAT', rate: 20, amount: 19.8 }],
    tax_total: 19.8,
    subtotal_ex_tax: 99,
    grand_total: 118.8,
}
