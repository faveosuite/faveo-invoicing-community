jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: { id: '42' }, query: {} }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductVersionCreate from '@/pages/admin/products/ProductVersionCreate'

const STUBS = [
    'AppAlert', 'action-button', 'TextField', 'SelectField', 'Switch',
    'TinyMCE', 'TextArea', 'loader', 'inline-loader', 'AppButton',
]

describe('ProductVersionCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'uploaded-file.zip' })
        globalThis.mockHttp.onPut(/\/product\/upload\/42/).reply(200, { data: { message: 'Created' } })

        wrapper = mount(ProductVersionCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders a save button', () => {
        expect(wrapper.find('action-button-stub, app-button-stub, button').exists()).toBe(true)
    })

    it('initialises form with empty title and version', () => {
        expect(wrapper.vm.form.title).toBe('')
        expect(wrapper.vm.form.version).toBe('')
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })

    it('submit does not call PUT when required fields are missing', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    it('submit sets errors when title and version are empty', async () => {
        await wrapper.vm.submit()
        expect(wrapper.vm.errors.title).toBeTruthy()
        expect(wrapper.vm.errors.version).toBeTruthy()
    })

    it('submit sets file error when no file is selected', async () => {
        wrapper.vm.form.title = 'Test Title'
        wrapper.vm.form.version = '1.0.0'
        await wrapper.vm.submit()
        expect(wrapper.vm.errors.file).toBeTruthy()
    })

    it('pushes to product edit after successful submit', async () => {
        wrapper.vm.form.title = 'Test Title'
        wrapper.vm.form.version = '1.0.0'
        wrapper.vm.form.dependencies = '[]'
        // Simulate a file being set via vm (bypassing DOM file input)
        wrapper.vm.file = new File(['content'], 'test.zip', { type: 'application/zip' })
        await wrapper.vm.submit()
        await flushPromises()
        expect(mockPush).toHaveBeenCalledWith('/products/42/edit?tab=versions')
    })
})
