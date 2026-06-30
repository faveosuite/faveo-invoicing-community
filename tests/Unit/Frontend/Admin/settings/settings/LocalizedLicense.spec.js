jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LocalizedLicense from '@/pages/admin/settings/settings/LocalizedLicense.vue'

describe('LocalizedLicense.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onDelete(/\/localized-license\/files/).reply(200, { data: {} })

        wrapper = mount(LocalizedLicense, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'DeleteModal', 'inline-loader', 'loader',
                    'action-button',
                ],
            },
        })
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

    it('does not show delete modal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('shows delete modal when deleteFileName is set', async () => {
        wrapper.vm.deleteFileName = 'license-file.json'
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('has the correct DataTable url attribute', () => {
        const dt = wrapper.find('data-table-stub')
        expect(dt.attributes('url')).toContain('localized-license/files')
    })
})
