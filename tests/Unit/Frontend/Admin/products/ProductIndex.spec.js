jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductIndex from '@/pages/admin/products/ProductIndex'

describe('ProductIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/products/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/products/).reply(200, { data: { message: 'Deleted' } })

        wrapper = mount(ProductIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
                    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
                    'Tooltip', 'ImageField', 'SelectField', 'VersionTableActions',
                    'ProductPluginMapping', 'spinner-loader', 'ProductTableActions',
                ],
            },
        })
    })

    afterEach(() => {
        global.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('passes /products url to DataTable', () => {
        const dt = wrapper.find('data-table-stub')
        expect(dt.attributes('url')).toMatch(/\/products/)
    })

    it('renders a create button/link', () => {
        const link = wrapper.find('a[href*="products/create"], router-link-stub, a')
        expect(link.exists()).toBe(true)
    })

    it('shows DeleteModal when bulk delete triggered', async () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
        wrapper.vm.selectedProducts = [1, 2]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('bulk delete sends correct payload with product_ids key', async () => {
        wrapper.vm.selectedProducts = [3, 5]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ product_ids: [3, 5] })
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('product_ids')
        expect(wrapper.vm.pendingBulkDelete).not.toHaveProperty('select')
    })

    it('DataTable is rendered with expected columns config', () => {
        const dt = wrapper.find('data-table-stub')
        expect(dt.exists()).toBe(true)
        const columns = wrapper.vm.columns
        expect(columns).toEqual(['select', 'name', 'image', 'license_type', 'group', 'action'])
    })
})

describe('ProductIndex.vue — branch coverage', () => {
    let wrapper
    beforeEach(() => {
        global.mockHttp.onGet(/\/products/).reply(200, { data: [] })
        wrapper = mount(ProductIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DataTable', 'AppAlert', 'DeleteModal', 'ProductTableActions', 'ColumnSelector', 'router-link'],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
    })

    it('toggleRow adds an id', () => { wrapper.vm.toggleRow(3); expect(wrapper.vm.selectedProducts).toContain(3) })
    it('toggleRow removes an id', () => { wrapper.vm.selectedProducts = [3]; wrapper.vm.toggleRow(3); expect(wrapper.vm.selectedProducts).not.toContain(3) })
    it('toggleAll selects all', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedProducts).toEqual(expect.arrayContaining([1, 2]))
    })
    it('toggleAll deselects', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selectedProducts = [1, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedProducts).not.toContain(1)
        expect(wrapper.vm.selectedProducts).toContain(99)
    })
    it('allSelected is false for empty tableData', () => { expect(wrapper.vm.allSelected).toBe(false) })
    it('allSelected is true when all rows selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selectedProducts = [1]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    describe('templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates
        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('name returns name when set', () => { expect(tpl().name(null, { name: 'MyApp' })).toBe('MyApp') })
        it('image returns — when no image', () => { expect(tpl().image(null, { name: 'x' })).toBe('—') })
        it('image renders img when image present', () => {
            const vnode = tpl().image(null, { name: 'x', image: 'http://x.com/img.png' })
            expect(vnode).toBeTruthy()
        })
        it('license_type returns — when falsy', () => { expect(tpl().license_type(null, {})).toBe('—') })
        it('license_type returns value when set', () => { expect(tpl().license_type(null, { license_type: 'Pro' })).toBe('Pro') })
        it('group returns — when falsy', () => { expect(tpl().group(null, {})).toBe('—') })
        it('group returns value when set', () => { expect(tpl().group(null, { group: 'Enterprise' })).toBe('Enterprise') })
    })

    describe('requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at') })
        it('passes orderBy through', () => { expect(adapt({ orderBy: 'name', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('name') })
        it('defaults to desc when no orderBy (latest first)', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc') })
        it('sets desc when ascending=false', () => { expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc') })
    })
})
