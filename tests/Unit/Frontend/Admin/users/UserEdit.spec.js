jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '1' }, query: {} }),
}))
jest.mock('@/validations/admin/userValidations', () => ({ userEditSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserEdit from '@/pages/admin/users/UserEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const userResponse = {
    data: {
        data: {
            first_name: 'John',
            last_name: 'Doe',
            email: 'john@example.com',
            user_name: 'johndoe',
            company: 'Acme Inc',
            active: 1,
            mobile_verified: 0,
        },
    },
}

describe('UserEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/user\/1/).reply(200, userResponse.data)
        globalThis.mockHttp.onPatch(/\/user\/1/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(UserEdit, {
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

    it('fetches user data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/user\/1/.test(r.url))).toBe(true)
    })

    it('populates form fields after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.first_name).toBe('John')
        expect(wrapper.vm.form.last_name).toBe('Doe')
        expect(wrapper.vm.form.email).toBe('john@example.com')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/user\/1/).reply(500)
        wrapper = mount(UserEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'SelectField',
                    'RadioButton', 'PhoneField', 'action-button', 'inline-loader', 'loader',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls PATCH /user/:id on submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => /\/user\/1/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on update failure', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPatch(/\/user\/1/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('onChange updates form fields', async () => {
        await flushPromises()
        wrapper.vm.onChange('Jane', 'first_name')
        expect(wrapper.vm.form.first_name).toBe('Jane')
    })

    it('onCountryChange clears state', async () => {
        await flushPromises()
        wrapper.vm.form.state = { id: 3 }
        wrapper.vm.onCountryChange({ code: 'IN', id: 2 })
        expect(wrapper.vm.form.state).toBeNull()
    })

    it('onRoleChange clears position', async () => {
        await flushPromises()
        wrapper.vm.form.position = { id: 'manager' }
        wrapper.vm.onRoleChange({ id: 'admin' })
        expect(wrapper.vm.form.position).toBeNull()
    })

    it('does not submit when validateForm returns false', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPatch(/\/user\/1/).reply(200, { data: {} })
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.length).toBe(0)
    })
})
