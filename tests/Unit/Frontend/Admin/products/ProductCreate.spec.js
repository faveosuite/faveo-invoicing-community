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
    'Tooltip', 'ImageField', 'SelectField', 'VersionTableActions',
    'ProductPluginMapping', 'spinner-loader',
]

describe('ProductCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.useFakeTimers()

        global.mockHttp.onGet(/\/dependency\/tax-classes/).reply(200, {
            data: { tax_classes: [{ id: 1, name: 'Standard' }] },
        })
        global.mockHttp.onPut(/\/product$/).reply(201, { data: { id: 1 } })

        wrapper = mount(ProductCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    afterEach(() => {
        global.mockHttp.reset()
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
            global.mockHttp.history.get.some(r => /\/dependency\/tax-classes/.test(r.url))
        ).toBe(true)
    })

    it('submits PUT /product with FormData on valid form', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(
            global.mockHttp.history.put.some(r => /\/product$/.test(r.url))
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
        global.mockHttp.reset()
        global.mockHttp.onPut(/\/product$/).reply(500)
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
})
