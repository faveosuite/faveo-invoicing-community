import {
    userFixture,
    emptyCartFixture,
    cartWithItemFixture,
    cartWithCouponFixture,
    checkoutCartFixture,
    datetimeSettingsFixture,
} from '../fixtures/index.js'

/**
 * Seed a MockAdapter instance with default happy-path responses.
 * Every test gets these by default — override per-test with:
 *   global.mockHttp.onGet('/my-cart').replyOnce(500, { message: 'Error' })
 *
 * @param {import('axios-mock-adapter').default} mock
 */
export function registerDefaultHandlers(mock) {
    // Auth
    mock.onGet('/api/user').reply(200, { data: userFixture })
    mock.onPost('/logout').reply(200, { success: true, message: 'Logged out.' })
    mock.onPost('/login').reply(200, { success: true, data: userFixture })

    // Cart
    mock.onGet('/my-cart').reply(200, { data: emptyCartFixture })
    mock.onGet('/my-cart/checkout').reply(200, { data: checkoutCartFixture })
    mock.onPost('/my-cart/items').reply(200, { data: cartWithItemFixture })
    mock.onPut(/\/my-cart\/items\/\d+/).reply(200, { data: cartWithItemFixture })
    mock.onDelete(/\/my-cart\/items\/\d+/).reply(200, { data: emptyCartFixture })
    mock.onDelete('/my-cart').reply(200, { data: emptyCartFixture })
    mock.onPost('/my-cart/coupon').reply(200, { data: cartWithCouponFixture })
    mock.onDelete('/my-cart/coupon').reply(200, { data: cartWithItemFixture })

    // Settings
    mock.onGet('/api/datetime-settings').reply(200, { data: datetimeSettingsFixture })
}
