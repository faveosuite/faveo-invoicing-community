jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CouponIndex from '@/pages/admin/products/coupons/CouponIndex.vue'

describe('CouponIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/promotions/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/promotions/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(CouponIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button',
                    'DeleteModal', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('confirmBulkDelete sets pendingBulkDelete when coupons are selected', () => {
        wrapper.vm.selectedCoupons = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('select')
    })

    it('confirmBulkDelete does nothing when no coupons are selected', () => {
        wrapper.vm.selectedCoupons = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected coupon ids', () => {
        wrapper.vm.selectedCoupons = [3, 7]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.select).toEqual([3, 7])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selectedCoupons = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    // ── toggleRow / toggleAll / allSelected ─────────────────────────
    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(5)
        expect(wrapper.vm.selectedCoupons).toContain(5)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selectedCoupons = [5]
        wrapper.vm.toggleRow(5)
        expect(wrapper.vm.selectedCoupons).not.toContain(5)
    })

    it('toggleAll selects all tableData rows when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedCoupons).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAll does not duplicate already-selected ids', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedCoupons = [1]
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedCoupons.filter(id => id === 1)).toHaveLength(1)
    })

    it('toggleAll deselects only tableData rows when unchecked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedCoupons = [1, 2, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedCoupons).not.toContain(1)
        expect(wrapper.vm.selectedCoupons).toContain(99)
    })

    it('allSelected is false when tableData is empty', () => {
        wrapper.vm.dtRef = { tableData: [], refresh: jest.fn() }
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('allSelected is true when every tableData row is selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedCoupons = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    it('allSelected is false when some rows are not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedCoupons = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    // ── templates ────────────────────────────────────────────────────
    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('code returns — when falsy', () => { expect(tpl().code(null, {})).toBe('—') })
        it('code returns the code when present', () => { expect(tpl().code(null, { code: 'SAVE10' })).toBe('SAVE10') })

        it('type returns — when no promotion_type', () => { expect(tpl().type(null, {})).toBe('—') })
        it('type returns name when present', () => { expect(tpl().type(null, { promotion_type: { name: 'Percentage' } })).toBe('Percentage') })

        it('value returns — when falsy', () => { expect(tpl().value(null, {})).toBe('—') })
        it('value returns the value when present', () => { expect(tpl().value(null, { value: 10 })).toBe(10) })

        it('products returns — when null', () => { expect(tpl().products(null, { products: null })).toBe('—') })
        it('products returns — when empty array', () => { expect(tpl().products(null, { products: [] })).toBe('—') })
        it('products renders RouterLink when product has id', () => {
            const vnode = tpl().products(null, { products: [{ id: 1, name: 'MyProduct' }] })
            expect(vnode).toBeTruthy()
        })
        it('products renders plain name when product has no id', () => {
            const vnode = tpl().products(null, { products: [{ id: null, name: 'MyProduct' }] })
            expect(vnode).toBeTruthy()
        })
        it('products normalizes a single object (not array) from hasOneThrough', () => {
            const vnode = tpl().products(null, { products: { id: 2, name: 'Solo' } })
            expect(vnode).toBeTruthy()
        })

        it('uses returns — when null', () => { expect(tpl().uses(null, {})).toBe('—') })
        it('uses returns count when present', () => { expect(tpl().uses(null, { uses: 5 })).toBe(5) })

        it('start returns — when falsy', () => { expect(tpl().start(null, {})).toBe('—') })
        it('start returns date substring when present', () => { expect(tpl().start(null, { start: '2025-01-15T00:00:00' })).toBe('2025-01-15') })

        it('expiry returns — when falsy', () => { expect(tpl().expiry(null, {})).toBe('—') })
        it('expiry returns date substring when present', () => { expect(tpl().expiry(null, { expiry: '2025-12-31T00:00:00' })).toBe('2025-12-31') })
    })

    // ── requestAdapter ───────────────────────────────────────────────
    describe('tableOptions.requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at when orderBy is undefined', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at')
        })
        it('passes orderBy through when provided', () => {
            expect(adapt({ orderBy: 'code', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('code')
        })
        it('defaults to desc sort-order when no orderBy (latest first)', () => {
            expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('sets desc sort-order when ascending is false', () => {
            expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc')
        })
        it('trims the search query', () => {
            expect(adapt({ ascending: true, query: '  abc  ', page: 1, limit: 10 })['search-query']).toBe('abc')
        })
    })
})
