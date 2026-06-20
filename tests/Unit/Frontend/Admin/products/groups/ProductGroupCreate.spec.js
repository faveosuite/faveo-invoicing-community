jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/productGroupValidations', () => ({ productGroupSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductGroupCreate from '@/pages/admin/products/groups/ProductGroupCreate.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('ProductGroupCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onPut(/\/group/).reply(200, { data: { message: 'Created' } })
        wrapper = mount(ProductGroupCreate, {
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

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders the save action-button', () => {
        expect(wrapper.find('action-button-stub').exists()).toBe(true)
    })

    it('calls PUT /group on submit', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.put.some(r => /\/group/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful create', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPut(/\/group/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('onChange updates form fields', () => {
        wrapper.vm.onChange('My Group', 'name')
        expect(wrapper.vm.form.name).toBe('My Group')
    })

    it('does not submit when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.put.length).toBe(0)
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })
})
