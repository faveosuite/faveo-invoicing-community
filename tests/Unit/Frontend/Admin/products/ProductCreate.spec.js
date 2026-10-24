jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: {}, query: {} }) }))
jest.mock('@/validations/admin/productValidations', () => ({ productSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductCreate from '@/pages/admin/products/ProductCreate'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'ImageField', 'DynamicSelect', 'VersionTableActions',
    'ProductPluginMapping', 'spinner-loader',
]

describe('ProductCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.useFakeTimers()

        globalThis.mockHttp.onGet(/\/dependency\/tax-classes/).reply(200, {
            data: { tax_classes: [{ id: 1, name: 'Standard' }] },
        })
        globalThis.mockHttp.onPost(/\/product$/).reply(201, { data: { id: 1 } })

        wrapper = mount(ProductCreate, {
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
        expect(wrapper.find('action-button-stub').exists()).toBe(true)
    })

    it('fetches tax-classes on mount', async () => {
        await flushPromises()
        expect(
            globalThis.mockHttp.history.get.some(r => /\/dependency\/tax-classes/.test(r.url))
        ).toBe(true)
    })

    it('submits POST /product with FormData on valid form', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(
            globalThis.mockHttp.history.post.some(r => /\/product$/.test(r.url))
        ).toBe(true)
    })

    it('calls successHandler on 201 success', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on 500 error', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/product$/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('redirects to /products after successful submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        // router.push is called inside a setTimeout — advance timers
        jest.runAllTimers()
        expect(mockPush).toHaveBeenCalledWith('/products')
    })

    it('sends every field, with switches as 1/0, in a fixed order', async () => {
        await flushPromises()
        Object.assign(wrapper.vm.form, {
            name: 'P', type: null, product_type: 'addon', config_file_path: 'c', license_file_path: null, product_path: 'pp',
            group: 3, parent: null, description: 'd', short_description: 's', product_description: 'pd', product_sku: 'SKU',
            show_agent: true, highlight: 0, add_to_contact: 1, can_modify_agent: false, can_modify_quantity: true,
            require_domain: null, hidden: 1, invoice_hidden: 0, whatsapp_integration: true, tax_status: 1, tax_class_id: 7,
        })

        await wrapper.vm.submit()
        await flushPromises()

        const fd = globalThis.mockHttp.history.post.find(r => /\/product$/.test(r.url)).data
        expect([...fd.entries()]).toEqual([
            ['name', 'P'], ['type', ''], ['product_type', 'addon'], ['config_file_path', 'c'], ['license_file_path', ''],
            ['product_path', 'pp'], ['group', '3'], ['parent', ''], ['description', 'd'], ['short_description', 's'],
            ['product_description', 'pd'], ['product_sku', 'SKU'],
            ['show_agent', '1'], ['highlight', '0'], ['add_to_contact', '1'], ['can_modify_agent', '0'],
            ['can_modify_quantity', '1'], ['require_domain', '0'], ['hidden', '1'], ['invoice_hidden', '0'],
            ['whatsapp_integration', '1'], ['tax_status', '1'], ['tax_class_id', '7'],
        ])
    })

    it('drops the product path for non-addons and the tax class when tax is off', async () => {
        await flushPromises()
        Object.assign(wrapper.vm.form, { product_type: 'product', product_path: 'pp', tax_status: 0, tax_class_id: 7 })

        await wrapper.vm.submit()
        await flushPromises()

        const fd = globalThis.mockHttp.history.post.find(r => /\/product$/.test(r.url)).data
        expect(fd.get('product_path')).toBe('')
        expect(fd.get('tax_status')).toBe('0')
        expect(fd.has('tax_class_id')).toBe(false)
    })
})
