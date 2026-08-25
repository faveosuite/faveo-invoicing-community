jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '7' }, query: {} }) }))
jest.mock('@/validations/admin/emailValidations', () => ({
    buildEmailSettingsSchema: jest.fn(() => ({})),
    templateEditSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import TemplateEdit from '@/pages/admin/settings/email/TemplateEdit.vue'

describe('TemplateEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/template\/edit\/7/).reply(200, {
            data: {
                template: {
                    name: 'Welcome', type: '1', reply_to: '', data: '<p>Hello</p>',
                },
                codes: { '{name}': 'Customer Name' },
                type: { '1': 'welcome_mail', '2': 'invoice_mail' },
            },
        })
        globalThis.mockHttp.onPut(/\/template\/update\/7/).reply(200, { message: 'Updated' })

        wrapper = mount(TemplateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'TinyMCE', 'action-button',
                    'loader', 'inline-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches template edit data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/template\/edit\/7/)
    })

    it('handles 500 error on fetch', async () => {
        globalThis.mockHttp.onGet(/\/template\/edit\/7/).reply(500)
        const w = mount(TemplateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'DynamicSelect', 'TinyMCE', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via PUT to /template/update/:id', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.save()
        await flushPromises()

        expect(globalThis.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.put[0].url).toMatch(/\/template\/update\/7/)
    })

    it('calls successHandler after successful save', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.save()
        await flushPromises()

        expect(successHandler).toHaveBeenCalled()
    })
})
