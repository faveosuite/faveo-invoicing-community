jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { name: 'Toggle', template: '<button />', props: ['modelValue', 'disabled'], emits: ['update:modelValue'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/widgetValidations', () => ({ footerWidgetSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import Footer from '@/pages/admin/settings/widgets/Footer.vue'

describe('Footer.vue', () => {
    let wrapper

    const widgetListResponse = {
        data: {
            pages: {
                data: [
                    { id: 1, type: 'footer1' },
                    { id: 2, type: 'footer2' },
                    { id: 3, type: 'footer3' },
                ],
            },
        },
    }

    const widgetDetailResponse = {
        data: {
            widget: {
                name: 'Footer 1',
                publish: true,
                allow_mailchimp: false,
                allow_social_media: true,
                content: '<p>Footer content</p>',
            },
            mailchimpStatus: false,
        },
    }

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/widgets\/list/).reply(200, widgetListResponse)
        globalThis.mockHttp.onGet(/\/widgets\/show\/1/).reply(200, widgetDetailResponse)
        globalThis.mockHttp.onGet(/\/widgets\/show\/2/).reply(200, widgetDetailResponse)
        globalThis.mockHttp.onGet(/\/widgets\/show\/3/).reply(200, widgetDetailResponse)
        globalThis.mockHttp.onPut(/\/widgets\/update\//).reply(200, { message: 'Saved' })
        globalThis.mockHttp.onPost(/\/widgets\/create/).reply(200, { data: { id: 10 }, message: 'Created' })

        wrapper = mount(Footer, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'TinyMCE', 'Switch', 'action-button', 'loader', 'inline-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches widget list on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/widgets\/list/)
    })

    it('fetches widget details for each footer type after list loads', async () => {
        // sequential async loop — each iteration needs its own flush
        for (let i = 0; i < 5; i++) await flushPromises()
        const detailCalls = globalThis.mockHttp.history.get.filter(r => r.url.includes('/widgets/show/'))
        expect(detailCalls.length).toBe(3)
    })

    it('populates form data after successful fetch', async () => {
        for (let i = 0; i < 5; i++) await flushPromises()
        expect(wrapper.vm.forms.footer1.name).toBe('Footer 1')
    })

    it('calls errorHandler on fetch failure', async () => {
        globalThis.mockHttp.onGet(/\/widgets\/list/).reply(500)
        const w = mount(Footer, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'TinyMCE', 'Switch', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits PUT when widgetId exists for footer type', async () => {
        await flushPromises()
        await wrapper.vm.save('footer1')
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.put[0].url).toMatch(/\/widgets\/update\/1/)
    })

    it('calls successHandler on successful save', async () => {
        await flushPromises()
        await wrapper.vm.save('footer1')
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        globalThis.mockHttp.onPut(/\/widgets\/update\//).reply(500)
        await flushPromises()
        await wrapper.vm.save('footer1')
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not submit when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await flushPromises()
        const putCountBefore = globalThis.mockHttp.history.put.length
        await wrapper.vm.save('footer1')
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBe(putCountBefore)
    })

    it('initializes with footer1 as active tab', () => {
        expect(wrapper.vm.activeTab).toBe('footer1')
    })

    it('switches active tab when tab is changed', async () => {
        wrapper.vm.activeTab = 'footer2'
        expect(wrapper.vm.activeTab).toBe('footer2')
    })
})
