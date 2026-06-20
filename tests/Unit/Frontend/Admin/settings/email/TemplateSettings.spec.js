jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import TemplateSettings from '@/pages/admin/settings/email/TemplateSettings.vue'

describe('TemplateSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/settings\/template/).reply(200, {
            data: {
                types: [
                    { id: 1, name: 'welcome_mail', selected_template_id: null },
                    { id: 2, name: 'invoice_mail', selected_template_id: 3 },
                ],
                templates: [
                    { id: 1, name: 'Welcome Template' },
                    { id: 2, name: 'Invoice Template' },
                    { id: 3, name: 'Custom Invoice' },
                ],
            },
        })
        global.mockHttp.onPatch(/\/settings\/template/).reply(200, { message: 'Saved' })

        wrapper = mount(TemplateSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'SelectField', 'action-button',
                    'loader', 'inline-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches template settings on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/settings\/template/)
    })

    it('handles 500 error on fetch', async () => {
        global.mockHttp.onGet(/\/settings\/template/).reply(500)
        const w = mount(TemplateSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SelectField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits mappings via PATCH to /settings/template', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()

        expect(global.mockHttp.history.patch.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.patch[0].url).toMatch(/\/settings\/template/)
    })

    it('calls successHandler after successful submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })

    it('onSelect updates mappings correctly', async () => {
        await flushPromises()
        wrapper.vm.onSelect(1, { id: 2, name: 'Invoice Template' })
        expect(wrapper.vm.mappings[1]).toBe(2)
    })
})
