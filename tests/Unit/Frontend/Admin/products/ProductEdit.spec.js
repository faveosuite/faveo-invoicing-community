jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: mockPush }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))
jest.mock('@/validations/admin/productValidations', () => ({ productSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductEdit from '@/pages/admin/products/ProductEdit'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'ImageField', 'DynamicSelect', 'VersionTableActions',
    'ProductPluginMapping', 'spinner-loader',
]

const productResponse = {
    data: {
        id: 1,
        name: 'Test Product',
        product_sku: 'SKU-001',
        status: 1,
        tax_status: 1,
        tax_class_id: 1,
        github_status: false,
        taxes: [],
    },
}

describe('ProductEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.useFakeTimers()

        globalThis.mockHttp.onGet(/\/dependency\/tax-classes/).reply(200, {
            data: { tax_classes: [{ id: 1, name: 'Standard' }] },
        })
        globalThis.mockHttp.onGet(/\/product\/0/).reply(200, productResponse)
        globalThis.mockHttp.onGet(/\/dependency\/product-plans/).reply(200, { data: { data: [], total: 0 } })
        globalThis.mockHttp.onGet(/\/product\/uploads\/0/).reply(200, { data: { data: [], total: 0 } })
        globalThis.mockHttp.onPost(/\/product\/0/).reply(200, { data: { id: 1 } })

        wrapper = mount(ProductEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
        jest.useRealTimers()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the form', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('fetches tax-classes and product data on mount', async () => {
        await flushPromises()
        const urls = globalThis.mockHttp.history.get.map(r => r.url)
        expect(urls.some(u => /\/dependency\/tax-classes/.test(u))).toBe(true)
        expect(urls.some(u => /\/product\/0/.test(u))).toBe(true)
    })

    it('populates form with fetched product data', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('Test Product')
        expect(wrapper.vm.form.product_sku).toBe('SKU-001')
    })

    it('submits POST /product/0 on valid form', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => /\/product\/0/.test(r.url))
        ).toBe(true)
    })

    it('calls successHandler on success', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on 500 error', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/product\/0/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('redirects to /products after successful submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        jest.runAllTimers()
        expect(mockPush).toHaveBeenCalledWith('/products')
    })

    it('onChange sets form.type and typeObj for type field', async () => {
        await flushPromises()
        wrapper.vm.onChange({ id: 'cloud' }, 'type')
        expect(wrapper.vm.form.type).toBe('cloud')
        expect(wrapper.vm.form.typeObj).toEqual({ id: 'cloud' })
    })

    it('onChange sets form.type to null when val is null', async () => {
        await flushPromises()
        wrapper.vm.onChange(null, 'type')
        expect(wrapper.vm.form.type).toBeNull()
    })

    it('onChange sets form.group for group field', async () => {
        await flushPromises()
        wrapper.vm.onChange({ id: 5 }, 'group')
        expect(wrapper.vm.form.group).toBe(5)
    })

    it('onChange sets form.parent for parent field', async () => {
        await flushPromises()
        wrapper.vm.onChange({ id: 3 }, 'parent')
        expect(wrapper.vm.form.parent).toBe(3)
    })

    it('onChange sets arbitrary form field', async () => {
        await flushPromises()
        wrapper.vm.onChange('test-sku', 'product_sku')
        expect(wrapper.vm.form.product_sku).toBe('test-sku')
    })

    it('onImageChange sets selectedImage and currentImage', async () => {
        await flushPromises()
        wrapper.vm.onImageChange({ image: 'data:image/png;base64,abc', name: 'test.png' })
        expect(wrapper.vm.form.currentImage).toBe('data:image/png;base64,abc')
    })

    it('onImageChange clears selectedImage when null', async () => {
        await flushPromises()
        wrapper.vm.onImageChange(null)
        expect(wrapper.vm.selectedImage).toBeNull()
    })

    it('toggleVersion adds id when not selected', async () => {
        await flushPromises()
        wrapper.vm.selectedVersions = []
        wrapper.vm.toggleVersion(99)
        expect(wrapper.vm.selectedVersions).toContain(99)
    })

    it('toggleVersion removes id when already selected', async () => {
        await flushPromises()
        wrapper.vm.selectedVersions = [99]
        wrapper.vm.toggleVersion(99)
        expect(wrapper.vm.selectedVersions).not.toContain(99)
    })

    it('confirmBulkDeleteVersions sets pending when versions selected', async () => {
        await flushPromises()
        wrapper.vm.selectedVersions = [1, 2]
        wrapper.vm.confirmBulkDeleteVersions()
        expect(wrapper.vm.pendingDeleteVersions).not.toBeNull()
    })

    it('confirmBulkDeleteVersions does nothing when no versions selected', async () => {
        await flushPromises()
        wrapper.vm.selectedVersions = []
        wrapper.vm.confirmBulkDeleteVersions()
        expect(wrapper.vm.pendingDeleteVersions).toBeNull()
    })
})
