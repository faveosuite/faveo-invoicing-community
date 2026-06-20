jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import TaxIndex from '@/pages/admin/settings/common/tax/TaxIndex.vue'

describe('TaxIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                options: { tax_enable: 1, inclusive: 0, rounding: 0, tax_based_on: 'billing' },
                additional_tax_classes: '',
                classes: [{ slug: '', name: 'Standard' }],
            },
        })
        global.mockHttp.onPost(/\/taxes\/option/).reply(200, { message: 'Saved' })

        wrapper = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'SelectField', 'action-button',
                    'DeleteModal', 'loader', 'inline-loader', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches tax options on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/tax-options/)
    })

    it('handles 500 error on loadOptions', async () => {
        global.mockHttp.onGet(/\/tax-options/).reply(500)
        const w = mount(TaxIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'SelectField', 'action-button', 'DeleteModal', 'loader', 'inline-loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits options via POST to /taxes/option', async () => {
        await flushPromises()
        await wrapper.vm.saveOptions()
        await flushPromises()

        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.post[0].url).toMatch(/\/taxes\/option/)
    })

    it('confirmBulkDelete sets pendingBulkDelete when items are selected', async () => {
        await flushPromises()
        wrapper.vm.selected = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toEqual({ select: [1, 2] })
    })

    it('renders DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() || wrapper.html().includes('datatable')).toBeTruthy()
    })
})
