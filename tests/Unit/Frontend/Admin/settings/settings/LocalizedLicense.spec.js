jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import LocalizedLicense from '@/pages/admin/settings/settings/LocalizedLicense.vue'

describe('LocalizedLicense.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        globalThis.mockHttp.onPost(/\/localized-license\/bulk-disable/).reply(200, { data: {} })

        wrapper = mount(LocalizedLicense, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'loader',
                    'action-button', 'spinner-loader',
                ],
            },
        })
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the localized license card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('has the correct DataTable url attribute', () => {
        const dt = wrapper.find('data-table-stub')
        expect(dt.attributes('url')).toContain('localized-license/orders')
    })

    it('selected starts as empty array', () => {
        expect(wrapper.vm.selected).toEqual([])
    })

    it('disabling starts as false', () => {
        expect(wrapper.vm.disabling).toBe(false)
    })

    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(3)
        expect(wrapper.vm.selected).toContain(3)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selected = [3]
        wrapper.vm.toggleRow(3)
        expect(wrapper.vm.selected).not.toContain(3)
    })

    it('bulkDisable does nothing when selection is empty', async () => {
        wrapper.vm.selected = []
        await wrapper.vm.bulkDisable()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('bulkDisable posts selected ids to bulk-disable', async () => {
        wrapper.vm.selected = [1, 2]
        await wrapper.vm.bulkDisable()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(1)
        expect(globalThis.mockHttp.history.post[0].url).toMatch(/localized-license\/bulk-disable/)
    })

    it('bulkDisable calls successHandler and clears selection on success', async () => {
        wrapper.vm.selected = [1, 2]
        await wrapper.vm.bulkDisable()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
        expect(wrapper.vm.selected).toEqual([])
    })

    it('bulkDisable calls errorHandler on API failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/localized-license\/bulk-disable/).reply(500)
        wrapper.vm.selected = [1]
        await wrapper.vm.bulkDisable()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('bulkDisable sets disabling false after request regardless of outcome', async () => {
        wrapper.vm.selected = [1]
        await wrapper.vm.bulkDisable()
        await flushPromises()
        expect(wrapper.vm.disabling).toBe(false)
    })

    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('license_domain returns — when falsy', () => {
            expect(tpl().license_domain(null, {})).toBe('—')
        })

        it('license_domain returns the value when present', () => {
            expect(tpl().license_domain(null, { license_domain: 'example.com' })).toBe('example.com')
        })

        it('client_email returns — when there is no client', () => {
            expect(tpl().client_email(null, {})).toBe('—')
        })

        it('client_email renders a link when client_id is present', () => {
            const vnode = tpl().client_email(null, { client_email: 'a@b.com', client_id: 1 })
            expect(vnode).toBeTruthy()
        })

        it('product_name returns — when there is no product', () => {
            expect(tpl().product_name(null, {})).toBe('—')
        })

        it('is_bound renders configured badge when true', () => {
            const vnode = tpl().is_bound(null, { is_bound: true })
            expect(vnode).toBeTruthy()
        })
    })
})
