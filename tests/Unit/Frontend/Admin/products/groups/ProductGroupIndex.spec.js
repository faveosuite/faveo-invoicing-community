jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductGroupIndex from '@/pages/admin/products/groups/ProductGroupIndex.vue'

describe('ProductGroupIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/groups/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/group/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(ProductGroupIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button',
                    'DeleteModal', 'router-link',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders a DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('renders AppAlert', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('does not show DeleteModal by default', () => {
        expect(wrapper.find('delete-modal-stub').exists()).toBe(false)
    })

    it('confirmBulkDelete sets pendingBulkDelete when groups are selected', () => {
        wrapper.vm.selectedGroups = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('select')
    })

    it('confirmBulkDelete does nothing when no groups are selected', () => {
        wrapper.vm.selectedGroups = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected group ids', () => {
        wrapper.vm.selectedGroups = [4, 9]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.select).toEqual([4, 9])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selectedGroups = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })
})

describe('ProductGroupIndex.vue — branch coverage', () => {
    let wrapper
    beforeEach(() => {
        global.mockHttp.onGet(/\/product-groups/).reply(200, { data: [] })
        wrapper = mount(ProductGroupIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DataTable', 'AppAlert', 'DeleteModal', 'router-link'],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
    })

    it('toggleRow adds id', () => { wrapper.vm.toggleRow(9); expect(wrapper.vm.selectedGroups).toContain(9) })
    it('toggleRow removes id', () => { wrapper.vm.selectedGroups = [9]; wrapper.vm.toggleRow(9); expect(wrapper.vm.selectedGroups).not.toContain(9) })
    it('toggleAll selects all', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selectedGroups).toEqual(expect.arrayContaining([1, 2]))
    })
    it('toggleAll deselects all from tableData', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selectedGroups = [1, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selectedGroups).not.toContain(1)
        expect(wrapper.vm.selectedGroups).toContain(99)
    })
    it('allSelected is false when no tableData', () => { expect(wrapper.vm.allSelected).toBe(false) })
    it('allSelected is true when all selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selectedGroups = [1]
        expect(wrapper.vm.allSelected).toBe(true)
    })
    it('allSelected is false when some not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selectedGroups = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    describe('templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates
        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('name returns value when set', () => { expect(tpl().name(null, { name: 'Enterprise' })).toBe('Enterprise') })
    })

    describe('requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at') })
        it('passes orderBy through', () => { expect(adapt({ orderBy: 'name', ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('name') })
        it('sets asc when ascending=true', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('asc') })
        it('sets desc when ascending=false', () => { expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc') })
    })
})
