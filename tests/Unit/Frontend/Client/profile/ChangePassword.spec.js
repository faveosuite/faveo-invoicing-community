jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/client/profile.js', () => ({ passwordChangeSchema: {}, profileSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import { validateForm } from '@/helpers/formUtils.js'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import ChangePassword from '@/pages/client/profile/ChangePassword.vue'

describe('ChangePassword.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        validateForm.mockResolvedValue(true)

        wrapper = mount(ChangePassword, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        auth: { user: { id: 1, first_name: 'John', email: 'john@example.com' }, isAuthenticated: true },
                    },
                })],
                stubs: ['app-card', 'client-field', 'app-alert'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a form', () => {
        expect(wrapper.find('form').exists()).toBeTruthy()
    })

    it('renders a submit button', () => {
        expect(wrapper.find('button[type="submit"]').exists()).toBeTruthy()
    })

    it('calls validateForm on form submit', async () => {
        axiosMock.onPost('/my-password').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(validateForm).toHaveBeenCalled()
    })

    it('does not call API when validation fails', async () => {
        validateForm.mockResolvedValueOnce(false)

        let postCalled = false
        axiosMock.onPost('/my-password').reply(() => {
            postCalled = true
            return [200, { data: {} }]
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(postCalled).toBe(false)
    })

    it('calls POST /my-password on valid submit', async () => {
        axiosMock.onPost('/my-password').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        const reqs = axiosMock.history.post.filter(r => r.url === '/my-password')
        expect(reqs.length).toBeGreaterThan(0)
    })

    it('calls successHandler on successful password change', async () => {
        axiosMock.onPost('/my-password').reply(200, { data: {} })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on server error', async () => {
        axiosMock.onPost('/my-password').reply(500, { message: 'Server error' })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not call errorHandler on 422 with field errors', async () => {
        axiosMock.onPost('/my-password').reply(422, {
            errors: { current_password: ['Wrong password.'] },
        })

        await wrapper.find('form').trigger('submit')
        await flushPromises()

        expect(errorHandler).not.toHaveBeenCalled()
    })

    it('submit button is not disabled when not saving', () => {
        const btn = wrapper.find('button[type="submit"]')
        // saving is false by default, so button should not be disabled
        expect(wrapper.vm.saving).toBe(false)
        expect(btn.element.disabled).toBe(false)
    })
})
