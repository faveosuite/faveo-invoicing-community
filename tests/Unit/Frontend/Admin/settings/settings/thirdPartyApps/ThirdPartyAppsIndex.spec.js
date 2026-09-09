jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/thirdPartyValidations', () => ({ thirdPartyAppSchema: {} }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ThirdPartyAppsIndex from '@/pages/admin/settings/settings/thirdPartyApps/ThirdPartyAppsIndex.vue'

describe('ThirdPartyAppsIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(ThirdPartyAppsIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'AppModal', 'TextField', 'action-button', 'DeleteModal', 'spinner-loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the DataTable stub', () => {
        expect(wrapper.html().toLowerCase()).toContain('data-table-stub')
    })

    it('calls POST to create app endpoint on saveApp', async () => {
        globalThis.mockHttp.onPost(/\/third-party-app-create/).reply(200, { data: {} })
        wrapper.vm.form.app_name = 'TestApp'
        wrapper.vm.form.app_key = 'key123'
        wrapper.vm.form.app_secret = 'secret123'
        wrapper.vm.saveApp()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('third-party-app-create'))).toBeTruthy()
    })

    it('calls PUT to update app endpoint when editId is set', async () => {
        globalThis.mockHttp.onPut(/\/third-party-app-update\/5/).reply(200, { data: {} })
        wrapper.vm.form.app_name = 'EditApp'
        wrapper.vm.form.app_key = 'key456'
        wrapper.vm.form.app_secret = 'secret456'
        wrapper.vm.editId = 5
        wrapper.vm.saveApp()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => r.url.includes('third-party-app-update/5'))).toBeTruthy()
    })

    it('openCreate resets form and shows create modal', () => {
        wrapper.vm.openCreate()
        expect(wrapper.vm.showCreate).toBe(true)
        expect(wrapper.vm.form.app_name).toBe('')
    })

    it('closeCreate hides create modal', () => {
        wrapper.vm.showCreate = true
        wrapper.vm.closeCreate()
        expect(wrapper.vm.showCreate).toBe(false)
    })
})
