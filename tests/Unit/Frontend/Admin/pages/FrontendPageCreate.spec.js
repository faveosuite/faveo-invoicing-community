jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/pageValidations', () => ({ buildFrontendPageCreateSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FrontendPageCreate from '@/pages/admin/pages/FrontendPageCreate.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('FrontendPageCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onPost(/\/page/).reply(200, { data: { message: 'Created' } })
        wrapper = mount(FrontendPageCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'DynamicSelect', 'StaticSelect',
                    'Switch', 'TinyMCE', 'action-button', 'inline-loader', 'loader',
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

    it('renders the form card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('renders the save action-button', () => {
        expect(wrapper.find('action-button-stub').exists()).toBe(true)
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })

    it('form initialises with empty values', () => {
        expect(wrapper.vm.form.name).toBe('')
        expect(wrapper.vm.form.slug).toBe('')
        expect(wrapper.vm.form.type).toBe('')
        expect(wrapper.vm.form.publish).toBe(false)
        expect(wrapper.vm.form.parent_page_id).toBeNull()
    })

    it('slug auto-derives from name (no spaces, lowercased)', async () => {
        wrapper.vm.form.name = 'My Test Page'
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.form.slug).toBe('mytestpage')
    })

    it('clears a stale slug error once name auto-derives a real slug', async () => {
        // Simulates: submit once with everything empty (slug gets a
        // "required" error since it's auto-derived, not typed), then type a
        // name — the auto-derived slug must not keep showing that error.
        wrapper.vm.setFieldError('slug', 'The slug field is required.')
        expect(wrapper.vm.errors.slug).toBeTruthy()

        wrapper.vm.form.name = 'My Test Page'
        await wrapper.vm.$nextTick()

        expect(wrapper.vm.form.slug).toBe('mytestpage')
        expect(wrapper.vm.errors.slug).toBeFalsy()
    })

    it('POSTs to /page on submit', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/page/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful create', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on create failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/page/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('does not POST when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        globalThis.mockHttp.reset()
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('onChange sets type and updates url when type is contactus', () => {
        wrapper.vm.onChange('contactus', 'type')
        expect(wrapper.vm.form.type).toBe('contactus')
        expect(wrapper.vm.form.url).toContain('contact-us')
    })
})
