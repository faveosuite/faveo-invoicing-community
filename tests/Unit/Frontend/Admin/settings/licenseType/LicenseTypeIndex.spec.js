jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot /></a>', name: 'RouterLink' },
}))
jest.mock('@/validations/admin/licenseTypeValidations', () => ({
    licenseTypeCreateSchema: {},
    licenseTypeEditSchema: {},
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { flushPromises } from '@vue/test-utils'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { validateForm } from '@/helpers/formUtils.js'
import LicenseTypeIndex from '@/pages/admin/settings/settings/licenseType/LicenseTypeIndex.vue'

const STUBS = [
    'AppAlert', 'AppModal', 'DataTable', 'DeleteModal',
    'TextField', 'action-button', 'spinner-loader', 'loader',
]

describe('LicenseTypeIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        jest.clearAllMocks()
        global.mockHttp.onGet(/\/get-license-type/).reply(200, { data: { id: 1, name: 'Standard' } })
        global.mockHttp.onPost(/\/create-license-type/).reply(200, { message: 'Created' })
        global.mockHttp.onPut(/\/update-license-type/).reply(200, { message: 'Updated' })
        global.mockHttp.onDelete(/\/delete-license-type/).reply(200, { message: 'Deleted' })

        wrapper = mount(LicenseTypeIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    // ── Initial state ────────────────────────────────────────────────
    it('showCreate starts as false', () => {
        expect(wrapper.vm.showCreate).toBe(false)
    })

    it('showEdit starts as false', () => {
        expect(wrapper.vm.showEdit).toBe(false)
    })

    it('selected starts as empty array', () => {
        expect(wrapper.vm.selected).toEqual([])
    })

    it('deleteId starts as null', () => {
        expect(wrapper.vm.deleteId).toBeNull()
    })

    // ── Create modal ─────────────────────────────────────────────────
    it('openCreate sets showCreate to true', () => {
        wrapper.vm.openCreate()
        expect(wrapper.vm.showCreate).toBe(true)
    })

    it('closeCreate sets showCreate to false and clears newName', () => {
        wrapper.vm.showCreate = true
        wrapper.vm.newName = 'Test'
        wrapper.vm.closeCreate()
        expect(wrapper.vm.showCreate).toBe(false)
        expect(wrapper.vm.newName).toBe('')
    })

    it('create() calls validateForm with the current newName', async () => {
        wrapper.vm.newName = 'Enterprise'
        await wrapper.vm.create()
        expect(validateForm).toHaveBeenCalledWith(
            expect.anything(),
            { license_type_name: 'Enterprise' },
            expect.any(Function),
        )
    })

    it('create() posts to create-license-type on validation pass', async () => {
        wrapper.vm.newName = 'Enterprise'
        await wrapper.vm.create()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.post[0].url).toMatch(/create-license-type/)
    })

    it('create() calls successHandler and closes modal on success', async () => {
        wrapper.vm.newName = 'Enterprise'
        await wrapper.vm.create()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
        expect(wrapper.vm.showCreate).toBe(false)
    })

    it('create() calls errorHandler on API failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/create-license-type/).reply(422, { message: 'Error' })
        wrapper.vm.newName = 'Enterprise'
        await wrapper.vm.create()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('create() sets creating false after request regardless of outcome', async () => {
        await wrapper.vm.create()
        await flushPromises()
        expect(wrapper.vm.creating).toBe(false)
    })

    it('create() skips the API call when validateForm returns false', async () => {
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.create()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    // ── Edit modal ───────────────────────────────────────────────────
    it('openEdit opens the modal and fetches license type data', async () => {
        await wrapper.vm.openEdit(1)
        await flushPromises()
        expect(wrapper.vm.showEdit).toBe(true)
        expect(wrapper.vm.editName).toBe('Standard')
        expect(wrapper.vm.editLoading).toBe(false)
    })

    it('openEdit calls errorHandler and closes modal on API failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/get-license-type\/\d+/).reply(500)
        await wrapper.vm.openEdit(1)
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        expect(wrapper.vm.showEdit).toBe(false)
    })

    it('closeEdit resets showEdit, editId, and editName', () => {
        wrapper.vm.showEdit = true
        wrapper.vm.editId = 5
        wrapper.vm.editName = 'Pro'
        wrapper.vm.closeEdit()
        expect(wrapper.vm.showEdit).toBe(false)
        expect(wrapper.vm.editId).toBeNull()
        expect(wrapper.vm.editName).toBe('')
    })

    it('update() puts to update-license-type on validation pass', async () => {
        wrapper.vm.editId = 1
        wrapper.vm.editName = 'Updated'
        await wrapper.vm.update()
        await flushPromises()
        expect(global.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.put[0].url).toMatch(/update-license-type\/1/)
    })

    it('update() calls successHandler and closes edit modal on success', async () => {
        wrapper.vm.editId = 1
        wrapper.vm.editName = 'Updated'
        await wrapper.vm.update()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
        expect(wrapper.vm.showEdit).toBe(false)
    })

    it('update() calls errorHandler on API failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPut(/\/update-license-type/).reply(500)
        wrapper.vm.editId = 1
        wrapper.vm.editName = 'Updated'
        await wrapper.vm.update()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('update() sets saving false after request regardless of outcome', async () => {
        wrapper.vm.editId = 1
        await wrapper.vm.update()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    it('update() skips the API call when validateForm returns false', async () => {
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.update()
        await flushPromises()
        expect(global.mockHttp.history.put.length).toBe(0)
    })

    // ── Delete ───────────────────────────────────────────────────────
    it('openDelete sets deleteId to the given id', () => {
        wrapper.vm.openDelete(7)
        expect(wrapper.vm.deleteId).toBe(7)
    })

    it('closeDelete sets deleteId to null', () => {
        wrapper.vm.deleteId = 7
        wrapper.vm.closeDelete()
        expect(wrapper.vm.deleteId).toBeNull()
    })

    it('onDeleted closes delete modal and refreshes table', () => {
        wrapper.vm.deleteId = 7
        wrapper.vm.onDeleted()
        expect(wrapper.vm.deleteId).toBeNull()
        expect(wrapper.vm.dtRef.refresh).toHaveBeenCalled()
    })

    // ── Bulk delete ──────────────────────────────────────────────────
    it('bulkDelete shows bulk delete modal when items are selected', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.bulkDelete()
        expect(wrapper.vm.showBulkDelete).toBe(true)
    })

    it('bulkDelete does nothing when selection is empty', () => {
        wrapper.vm.selected = []
        wrapper.vm.bulkDelete()
        expect(wrapper.vm.showBulkDelete).toBe(false)
    })

    it('onBulkDeleted hides modal, clears selection, and refreshes table', () => {
        wrapper.vm.showBulkDelete = true
        wrapper.vm.selected = [1, 2]
        wrapper.vm.onBulkDeleted()
        expect(wrapper.vm.showBulkDelete).toBe(false)
        expect(wrapper.vm.selected).toEqual([])
        expect(wrapper.vm.dtRef.refresh).toHaveBeenCalled()
    })

    // ── Row selection ────────────────────────────────────────────────
    it('toggleRow adds an id when not already selected', () => {
        wrapper.vm.toggleRow(3)
        expect(wrapper.vm.selected).toContain(3)
    })

    it('toggleRow removes an id when already selected', () => {
        wrapper.vm.selected = [3]
        wrapper.vm.toggleRow(3)
        expect(wrapper.vm.selected).not.toContain(3)
    })

    it('toggleAll selects all rows from tableData when checked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selected).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAll deselects only tableData rows when unchecked', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selected = [1, 2, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selected).not.toContain(1)
        expect(wrapper.vm.selected).not.toContain(2)
        expect(wrapper.vm.selected).toContain(99)
    })

    it('allSelected is false when tableData is empty', () => {
        wrapper.vm.dtRef = { tableData: [], refresh: jest.fn() }
        expect(wrapper.vm.allSelected).toBe(false)
    })

    it('allSelected is true when every tableData row is in selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selected = [1, 2]
        expect(wrapper.vm.allSelected).toBe(true)
    })

    it('allSelected is false when some rows are not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selected = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    // ── Templates ────────────────────────────────────────────────────
    describe('tableOptions.templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates

        it('name returns — when row.name is falsy', () => {
            expect(tpl().name(null, {})).toBe('—')
        })

        it('name returns the license type name when present', () => {
            expect(tpl().name(null, { name: 'Enterprise' })).toBe('Enterprise')
        })

        it('action renders a vnode with edit and delete buttons', () => {
            const vnode = tpl().action(null, { id: 1 })
            expect(vnode).toBeTruthy()
        })
    })

    // ── requestAdapter ───────────────────────────────────────────────
    describe('tableOptions.requestAdapter', () => {
        const adapt = (data) => wrapper.vm.tableOptions.requestAdapter(data)

        it('defaults sort-field to created_at when orderBy is undefined', () => {
            const result = adapt({ ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-field']).toBe('created_at')
        })

        it('passes orderBy through when provided', () => {
            const result = adapt({ orderBy: 'name', ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-field']).toBe('name')
        })

        it('defaults to desc sort-order when no orderBy (latest first)', () => {
            const result = adapt({ ascending: true, query: '', page: 1, limit: 10 })
            expect(result['sort-order']).toBe('desc')
        })

        it('sets sort-order to desc when ascending is false', () => {
            const result = adapt({ ascending: false, query: '', page: 1, limit: 10 })
            expect(result['sort-order']).toBe('desc')
        })

        it('trims the search query', () => {
            const result = adapt({ ascending: true, query: '  foo  ', page: 1, limit: 10 })
            expect(result['search-query']).toBe('foo')
        })
    })
})
