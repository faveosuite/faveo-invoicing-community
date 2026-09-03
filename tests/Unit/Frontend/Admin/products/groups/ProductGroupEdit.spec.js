jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
}))
jest.mock('@/validations/admin/productGroupValidations', () => ({ productGroupSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductGroupEdit from '@/pages/admin/products/groups/ProductGroupEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const groupResponse = {
    name: 'Enterprise',
    headline: 'Best value',
    tagline: 'For teams',
    hidden: 0,
    status: 1,
    pricing_templates_id: 2,
    pricing_template: { id: 2, name: 'Classic' },
}

describe('ProductGroupEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/group\/0/).reply(200, groupResponse)
        globalThis.mockHttp.onPost(/\/group\/0/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(ProductGroupEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'RadioButton',
                    'Switch', 'action-button', 'inline-loader', 'loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches group data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/group\/0/.test(r.url))).toBe(true)
    })

    it('populates form fields after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('Enterprise')
        expect(wrapper.vm.form.headline).toBe('Best value')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/group\/0/).reply(500)
        wrapper = mount(ProductGroupEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'RadioButton',
                    'Switch', 'action-button', 'inline-loader', 'loader',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls PATCH /group/0 on submit (via POST + _method=PATCH override)', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        const call = globalThis.mockHttp.history.post.find(r => /\/group\/0/.test(r.url))
        expect(call).toBeTruthy()
        expect(call.data.get('_method')).toBe('PATCH')
    })

    it('calls successHandler on successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/group\/0/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/group\/0/).reply(200, { data: {} })
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })
})
