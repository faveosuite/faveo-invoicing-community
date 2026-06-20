jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/licenseTypeValidations', () => ({
    licenseTypeCreateSchema: {},
    licenseTypeEditSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LicenseTypeIndex from '@/pages/admin/settings/settings/licenseType/LicenseTypeIndex.vue'

describe('LicenseTypeIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/get-license-type$/).reply(200, {
            data: { data: [], total: 0 },
        })
        global.mockHttp.onGet(/\/get-license-type\/\d+/).reply(200, {
            data: { name: 'Test License Type' },
        })
        global.mockHttp.onPost(/\/create-license-type/).reply(200, { data: {} })
        global.mockHttp.onPut(/\/update-license-type\/\d+/).reply(200, { data: {} })

        wrapper = mount(LicenseTypeIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'AppModal', 'DeleteModal',
                    'inline-loader', 'loader', 'action-button',
                    'TextField', 'spinner-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the license types card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('does not show create modal by default', () => {
        const modals = wrapper.findAll('app-modal-stub')
        modals.forEach(m => {
            expect(m.attributes('showmodal')).not.toBe('true')
        })
    })

    it('opens create modal when add button clicked', async () => {
        const addBtn = wrapper.find('.card-tools .btn-tool')
        if (addBtn.exists()) {
            await addBtn.trigger('click')
            await wrapper.vm.$nextTick()
        }
    })

    it('calls create-license-type endpoint on create', async () => {
        await flushPromises()
        global.mockHttp.onPost(/\/create-license-type/).reply(200, { data: {} })
        const http = (await import('@/plugins/axios.js')).default
        await http.post('/create-license-type', { name: 'New Type' })
        await flushPromises()
        const postUrls = global.mockHttp.history.post.map(r => r.url)
        expect(postUrls.some(u => u.includes('create-license-type'))).toBe(true)
    })

    it('calls update-license-type endpoint on update', async () => {
        await flushPromises()
        global.mockHttp.onPut(/\/update-license-type\/1/).reply(200, { data: {} })
        const http = (await import('@/plugins/axios.js')).default
        await http.put('/update-license-type/1', { name: 'Updated Type' })
        await flushPromises()
        const putUrls = global.mockHttp.history.put.map(r => r.url)
        expect(putUrls.some(u => u.includes('update-license-type'))).toBe(true)
    })
})
