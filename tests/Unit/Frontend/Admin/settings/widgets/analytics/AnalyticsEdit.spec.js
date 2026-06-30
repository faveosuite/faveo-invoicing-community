jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '5' }, query: {} }) }))
jest.mock('@/validations/admin/widgetValidations', () => ({ buildAnalyticsSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import AnalyticsEdit from '@/pages/admin/settings/widgets/analytics/AnalyticsEdit.vue'

describe('AnalyticsEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/chat\/show\/5/).reply(200, {
            data: {
                name: 'My Script',
                on_registration: 1,
                google_analytics: 0,
                google_analytics_tag: '',
                script: 'console.log("test")',
            },
        })
        globalThis.mockHttp.onPut(/\/chat\/update\/5/).reply(200, { message: 'Updated' })

        wrapper = mount(AnalyticsEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'SelectField', 'action-button', 'loader', 'inline-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches analytics entry on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/chat\/show\/5/)
    })

    it('populates form after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('My Script')
        expect(wrapper.vm.form.script).toBe('console.log("test")')
    })

    it('calls errorHandler when fetch fails', async () => {
        globalThis.mockHttp.onGet(/\/chat\/show\/5/).reply(500)
        const w = mount(AnalyticsEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'SelectField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via PUT to /chat/update/:id', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.put[0].url).toMatch(/\/chat\/update\/5/)
    })

    it('calls successHandler after successful update', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit failure', async () => {
        globalThis.mockHttp.onPut(/\/chat\/update\/5/).reply(500)
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
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })
})
