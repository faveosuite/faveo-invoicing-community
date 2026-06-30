jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: { id: '42', versionId: '7' }, query: {} }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductVersionEdit from '@/pages/admin/products/ProductVersionEdit'

const VERSION_RESPONSE = {
    data: {
        id: 7, title: 'Test Version', version: '2.0.0', description: 'A description',
        release_type: 'official', is_private: false, is_restricted: false,
        dependencies: [], file: 'existing-file.zip',
    },
}

const STUBS = [
    'AppAlert', 'action-button', 'TextField', 'SelectField', 'Switch',
    'TinyMCE', 'TextArea', 'loader', 'inline-loader',
]

describe('ProductVersionEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/product\/upload\/7/).reply(200, VERSION_RESPONSE)
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'new-file.zip' })
        globalThis.mockHttp.onPatch(/\/product\/upload\/7/).reply(200, { data: { message: 'Updated' } })

        wrapper = mount(ProductVersionEdit, {
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

    it('fetches version data on mount via GET /product/upload/:versionId', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/product\/upload\/7/.test(r.url))).toBe(true)
    })

    it('populates form from fetched version data', async () => {
        await flushPromises()
        expect(wrapper.vm.form.title).toBe('Test Version')
        expect(wrapper.vm.form.version).toBe('2.0.0')
    })

    it('loading starts true and becomes false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })

    it('submit does not call PATCH when required fields are empty', async () => {
        await flushPromises()
        wrapper.vm.form.title = ''
        wrapper.vm.form.version = ''
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.length).toBe(0)
    })

    it('calls PATCH /product/upload/:versionId on valid submit', async () => {
        await flushPromises()
        // form already populated from mock; no file needed for edit
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => /\/product\/upload\/7/.test(r.url))).toBe(true)
    })

    it('pushes to product edit after successful submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(mockPush).toHaveBeenCalledWith('/products/42/edit?tab=versions')
    })

    it('handles fetch failure gracefully', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/product\/upload\/7/).reply(500)
        const w = mount(ProductVersionEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
        await flushPromises()
        expect(w.vm.loading).toBe(false)
    })
})
