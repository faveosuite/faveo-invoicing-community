jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '3' }, query: {} }) }))
jest.mock('@/validations/admin/widgetValidations', () => ({ socialMediaSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import SocialMediaEdit from '@/pages/admin/settings/widgets/socialMedia/SocialMediaEdit.vue'

describe('SocialMediaEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/social-media\/show\/3/).reply(200, {
            data: { name: 'Twitter', link: 'https://twitter.com/company' },
        })
        global.mockHttp.onPatch(/\/social-media\/update\/3/).reply(200, { message: 'Updated' })

        wrapper = mount(SocialMediaEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader', 'inline-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches social media entry on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/social-media\/show\/3/)
    })

    it('populates form after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('Twitter')
        expect(wrapper.vm.form.link).toBe('https://twitter.com/company')
    })

    it('calls errorHandler when fetch fails', async () => {
        global.mockHttp.onGet(/\/social-media\/show\/3/).reply(500)
        const w = mount(SocialMediaEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via PATCH to /social-media/update/:id', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.patch.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.patch[0].url).toMatch(/\/social-media\/update\/3/)
    })

    it('calls successHandler after successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        global.mockHttp.onPatch(/\/social-media\/update\/3/).reply(500)
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.patch.length).toBe(0)
    })

    it('sets saving to false after submit completes', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})
