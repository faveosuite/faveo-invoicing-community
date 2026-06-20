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
