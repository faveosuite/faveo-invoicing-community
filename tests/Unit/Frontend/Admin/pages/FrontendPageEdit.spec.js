jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '0' }, query: {} }),
}))
jest.mock('@/validations/admin/pageValidations', () => ({ buildFrontendPageEditSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FrontendPageEdit from '@/pages/admin/pages/FrontendPageEdit.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const pageFixture = {
    id: 0,
    name: 'About Us',
    slug: 'about-us',
    url: '/about',
    type: '',
    publish: 1,
    content: '<p>About content</p>',
    parent_page_id: null,
    parent: null,
    is_default: 0,
    created_at: '2026-01-15T10:00:00Z',
}

describe('FrontendPageEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/page\/0/).reply(200, { data: pageFixture })
        globalThis.mockHttp.onPut(/\/page\/0/).reply(200, { data: { message: 'Updated' } })
        wrapper = mount(FrontendPageEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'StaticSelect',
                    'DatePicker', 'Switch', 'TinyMCE', 'action-button',
                    'inline-loader', 'loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('GETs /page/0 on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/page\/0/.test(r.url))).toBe(true)
    })

    it('populates form fields after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.form.name).toBe('About Us')
        expect(wrapper.vm.form.slug).toBe('about-us')
        expect(wrapper.vm.form.url).toBe('/about')
        expect(wrapper.vm.form.publish).toBe(true)
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('formats created_at_date from ISO string to YYYY-MM-DD', async () => {
        await flushPromises()
        expect(wrapper.vm.form.created_at_date).toBe('2026-01-15')
    })

    it('calls errorHandler when GET /page/0 fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/page\/0/).reply(500)
        wrapper = mount(FrontendPageEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'StaticSelect',
                    'DatePicker', 'Switch', 'TinyMCE', 'action-button',
                    'inline-loader', 'loader',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('PUTs to /page/0 on submit', async () => {
        await flushPromises()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => /\/page\/0/.test(r.url))).toBe(true)
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
        globalThis.mockHttp.onPut(/\/page\/0/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not PUT when validateForm returns false', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        globalThis.mockHttp.reset()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    it('onChange sets type and updates url when type is contactus', async () => {
        await flushPromises()
        wrapper.vm.onChange('contactus', 'type')
        expect(wrapper.vm.form.type).toBe('contactus')
        expect(wrapper.vm.form.url).toContain('contact-us')
    })
})
