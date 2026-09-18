jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/widgetValidations', () => ({ socialMediaSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import SocialMediaCreate from '@/pages/admin/settings/widgets/socialMedia/SocialMediaCreate.vue'

describe('SocialMediaCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onPost(/\/social-media\/create/).reply(200, { message: 'Created' })

        wrapper = mount(SocialMediaCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'action-button', 'inline-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the create form card', () => {
        expect(wrapper.find('.card').exists()).toBeTruthy()
    })

    it('initializes form with empty values', () => {
        expect(wrapper.vm.form.name).toBe('')
        expect(wrapper.vm.form.link).toBe('')
    })

    it('submits form via POST to /social-media/create', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.post[0].url).toMatch(/\/social-media\/create/)
    })

    it('calls successHandler after successful submit', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        globalThis.mockHttp.onPost(/\/social-media\/create/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('sets saving to false after submit completes', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})
