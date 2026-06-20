jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/userValidations', () => ({ userCreateSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserCreate from '@/pages/admin/users/UserCreate.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('UserCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onPut(/\/users/).reply(200, { data: { message: 'Created' } })
        wrapper = mount(UserCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'SelectField',
                    'RadioButton', 'PhoneField', 'action-button', 'inline-loader', 'loader',
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

    it('renders the form card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('renders the save action-button', () => {
        expect(wrapper.find('action-button-stub').exists()).toBe(true)
    })

    it('calls PUT /users on submit', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.put.some(r => /\/users/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful create', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on create failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPut(/\/users/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('onChange updates form fields', () => {
        wrapper.vm.onChange('John', 'first_name')
        expect(wrapper.vm.form.first_name).toBe('John')
    })

    it('onCountryChange updates country and clears state', () => {
        wrapper.vm.form.state = { id: 5 }
        wrapper.vm.onCountryChange({ code: 'US', id: 1 })
        expect(wrapper.vm.form.country).toEqual({ code: 'US', id: 1 })
        expect(wrapper.vm.form.state).toBeNull()
    })

    it('onRoleChange updates role and clears position', () => {
        wrapper.vm.form.position = { id: 'manager' }
        wrapper.vm.onRoleChange({ id: 'admin' })
        expect(wrapper.vm.form.role).toEqual({ id: 'admin' })
        expect(wrapper.vm.form.position).toBeNull()
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })

    it('does not submit when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.put.length).toBe(0)
    })
})
