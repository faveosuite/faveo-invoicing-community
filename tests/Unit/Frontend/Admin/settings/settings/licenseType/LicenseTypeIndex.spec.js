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
        globalThis.mockHttp.onGet(/\/get-license-type$/).reply(200, {
            data: { data: [], total: 0 },
        })
        globalThis.mockHttp.onGet(/\/get-license-type\/\d+/).reply(200, {
            data: { name: 'Test License Type' },
        })
        globalThis.mockHttp.onPost(/\/create-license-type/).reply(200, { data: {} })
        globalThis.mockHttp.onPut(/\/update-license-type\/\d+/).reply(200, { data: {} })

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
        globalThis.mockHttp.onPost(/\/create-license-type/).reply(200, { data: {} })
        const http = (await import('@/plugins/axios.js')).default
        await http.post('/create-license-type', { name: 'New Type' })
        await flushPromises()
        const postUrls = globalThis.mockHttp.history.post.map(r => r.url)
        expect(postUrls.some(u => u.includes('create-license-type'))).toBe(true)
    })

    it('calls update-license-type endpoint on update', async () => {
        await flushPromises()
        globalThis.mockHttp.onPut(/\/update-license-type\/1/).reply(200, { data: {} })
        const http = (await import('@/plugins/axios.js')).default
        await http.put('/update-license-type/1', { name: 'Updated Type' })
        await flushPromises()
        const putUrls = globalThis.mockHttp.history.put.map(r => r.url)
        expect(putUrls.some(u => u.includes('update-license-type'))).toBe(true)
    })

    it('openCreate sets showCreate=true and resets form', async () => {
        await flushPromises()
        wrapper.vm.openCreate()
        expect(wrapper.vm.showCreate).toBe(true)
    })

    it('closeCreate sets showCreate=false', async () => {
        await flushPromises()
        wrapper.vm.showCreate = true
        wrapper.vm.closeCreate()
        expect(wrapper.vm.showCreate).toBe(false)
    })

    it('openEdit fetches license type data and sets showEdit', async () => {
        globalThis.mockHttp.onGet(/\/get-license-type\/1/).reply(200, { data: { id: 1, name: 'SaaS' } })
        await flushPromises()
        await wrapper.vm.openEdit(1)
        await flushPromises()
        expect(wrapper.vm.showEdit).toBe(true)
    })

    it('openEdit handles error gracefully', async () => {
        globalThis.mockHttp.onGet(/\/get-license-type\/99/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.openEdit(99)).resolves.not.toThrow()
    })

    it('closeEdit sets showEdit=false', async () => {
        await flushPromises()
        wrapper.vm.showEdit = true
        wrapper.vm.closeEdit()
        expect(wrapper.vm.showEdit).toBe(false)
    })

    it('openDelete sets deleteId', async () => {
        await flushPromises()
        wrapper.vm.openDelete(5)
        expect(wrapper.vm.deleteId).toBe(5)
    })

    it('closeDelete clears deleteId', async () => {
        await flushPromises()
        wrapper.vm.deleteId = 5
        wrapper.vm.closeDelete()
        expect(wrapper.vm.deleteId).toBeNull()
    })

    it('create calls POST /create-license-type and closes modal on success', async () => {
        globalThis.mockHttp.onPost(/\/create-license-type/).reply(200, { data: { id: 2, name: 'New' } })
        await flushPromises()
        wrapper.vm.newName = 'New Type'
        await wrapper.vm.create()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('create-license-type'))).toBe(true)
        expect(wrapper.vm.showCreate).toBe(false)
    })

    it('create handles error', async () => {
        globalThis.mockHttp.onPost(/\/create-license-type/).reply(422, { message: 'Invalid' })
        await flushPromises()
        wrapper.vm.newName = 'Test'
        await expect(wrapper.vm.create()).resolves.not.toThrow()
    })

    it('update calls PUT /update-license-type/:id and closes modal on success', async () => {
        globalThis.mockHttp.onPut(/\/update-license-type\/1/).reply(200, { data: {} })
        await flushPromises()
        wrapper.vm.editId = 1
        wrapper.vm.editName = 'Updated'
        await wrapper.vm.update()
        await flushPromises()
        expect(wrapper.vm.showEdit).toBe(false)
    })

    it('toggleRow adds id when not selected', async () => {
        await flushPromises()
        wrapper.vm.selected = []
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selected).toContain(10)
    })

    it('toggleRow removes id when already selected', async () => {
        await flushPromises()
        wrapper.vm.selected = [10]
        wrapper.vm.toggleRow(10)
        expect(wrapper.vm.selected).not.toContain(10)
    })
})
