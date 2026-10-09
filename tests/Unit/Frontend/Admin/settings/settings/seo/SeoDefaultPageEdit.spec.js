jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { pageKey: 'login' }, query: {} }),
    RouterLink: { template: '<a><slot/></a>' },
}))
jest.mock('@/validations/admin/seoValidations', () => ({ seoDefaultPageSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SeoDefaultPageEdit from '@/pages/admin/settings/settings/seo/SeoDefaultPageEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const rowFixture = {
    meta_title: 'Login Page',
    meta_description: 'Sign in to your account',
    og_title: 'Login',
    og_description: 'Sign in',
    og_image: '',
    og_same_as_meta: 0,
}

describe('SeoDefaultPageEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/seo\/default-pages\/login/).reply(200, { data: rowFixture })
        globalThis.mockHttp.onPost(/\/seo\/default-pages\/login/).reply(200, { data: { message: 'Updated' } })

        wrapper = mount(SeoDefaultPageEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SeoFieldsCard', 'action-button', 'loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('shows the page label from the route param', () => {
        expect(wrapper.find('.card-title').text()).toContain('message.seo_login_and_register')
    })

    it('GETs the default page on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/seo\/default-pages\/login/.test(r.url))).toBe(true)
    })

    it('populates form fields after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.meta_title).toBe('Login Page')
        expect(wrapper.vm.form.meta_description).toBe('Sign in to your account')
        expect(wrapper.vm.form.og_title).toBe('Login')
        expect(wrapper.vm.form.og_description).toBe('Sign in')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when the fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/seo\/default-pages\/login/).reply(500)
        wrapper = mount(SeoDefaultPageEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SeoFieldsCard', 'action-button', 'loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submits via POST with a PATCH _method override', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        const call = globalThis.mockHttp.history.post.find(r => /\/seo\/default-pages\/login/.test(r.url))
        expect(call).toBeTruthy()
        expect(call.data.get('_method')).toBe('PATCH')
    })

    it('sends the current form fields in the request body', async () => {
        await flushPromises()
        wrapper.vm.form.meta_title = 'New Login Title'
        await wrapper.vm.submit()
        await flushPromises()
        const call = globalThis.mockHttp.history.post.find(r => /\/seo\/default-pages\/login/.test(r.url))
        expect(call.data.get('meta_title')).toBe('New Login Title')
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
        globalThis.mockHttp.onPost(/\/seo\/default-pages\/login/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/seo\/default-pages\/login/).reply(200, { data: {} })
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('onImageChange updates the OG image preview and selected file', () => {
        const file = new File(['x'], 'og.png', { type: 'image/png' })
        wrapper.vm.onImageChange({ image: 'blob:preview', file, name: 'og.png' })
        expect(wrapper.vm.ogImagePreview).toBe('blob:preview')
    })

    it('includes the selected OG image in the submit payload', async () => {
        await flushPromises()
        const file = new File(['x'], 'og.png', { type: 'image/png' })
        wrapper.vm.onImageChange({ image: 'blob:preview', file, name: 'og.png' })
        await wrapper.vm.submit()
        await flushPromises()
        const call = globalThis.mockHttp.history.post.find(r => /\/seo\/default-pages\/login/.test(r.url))
        expect(call.data.get('og_image')).toBeTruthy()
    })
})
