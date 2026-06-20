jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FrontendPageIndex from '@/pages/admin/pages/FrontendPageIndex.vue'

describe('FrontendPageIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/pages/).reply(200, { data: [] })
        global.mockHttp.onDelete(/\/pages/).reply(200, { data: { message: 'Deleted' } })
        wrapper = mount(FrontendPageIndex, {
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

    it('confirmBulkDelete sets pendingBulkDelete when pages are selected', () => {
        wrapper.vm.selected = [1, 2]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).not.toBeNull()
        expect(wrapper.vm.pendingBulkDelete).toHaveProperty('page_ids')
    })

    it('confirmBulkDelete does nothing when no pages are selected', () => {
        wrapper.vm.selected = []
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete).toBeNull()
    })

    it('pendingBulkDelete contains selected page ids', () => {
        wrapper.vm.selected = [3, 7]
        wrapper.vm.confirmBulkDelete()
        expect(wrapper.vm.pendingBulkDelete.page_ids).toEqual([3, 7])
    })

    it('shows DeleteModal when pendingBulkDelete is set', async () => {
        wrapper.vm.selected = [1]
        wrapper.vm.confirmBulkDelete()
        await wrapper.vm.$nextTick()
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })
})

describe('FrontendPageIndex.vue — branch coverage', () => {
    let wrapper
    beforeEach(() => {
        global.mockHttp.onGet(/\/pages/).reply(200, { data: [] })
        wrapper = mount(FrontendPageIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DataTable', 'AppAlert', 'DeleteModal', 'router-link'],
            },
        })
        wrapper.vm.dtRef = { refresh: jest.fn(), tableData: [] }
    })

    it('toggleRow adds an id', () => { wrapper.vm.toggleRow(5); expect(wrapper.vm.selected).toContain(5) })
    it('toggleRow removes an id', () => { wrapper.vm.selected = [5]; wrapper.vm.toggleRow(5); expect(wrapper.vm.selected).not.toContain(5) })
    it('toggleAll selects all rows', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.toggleAll({ target: { checked: true } })
        expect(wrapper.vm.selected).toEqual(expect.arrayContaining([1, 2]))
    })
    it('toggleAll deselects tableData rows', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selected = [1, 99]
        wrapper.vm.toggleAll({ target: { checked: false } })
        expect(wrapper.vm.selected).not.toContain(1)
        expect(wrapper.vm.selected).toContain(99)
    })
    it('allSelected is false for empty tableData', () => { expect(wrapper.vm.allSelected).toBe(false) })
    it('allSelected is true when all selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }], refresh: jest.fn() }
        wrapper.vm.selected = [1]
        expect(wrapper.vm.allSelected).toBe(true)
    })
    it('allSelected is false when some not selected', () => {
        wrapper.vm.dtRef = { tableData: [{ id: 1 }, { id: 2 }], refresh: jest.fn() }
        wrapper.vm.selected = [1]
        expect(wrapper.vm.allSelected).toBe(false)
    })

    describe('templates', () => {
        const tpl = () => wrapper.vm.tableOptions.templates
        it('name returns — when falsy', () => { expect(tpl().name(null, {})).toBe('—') })
        it('name returns name when set', () => { expect(tpl().name(null, { name: 'About' })).toBe('About') })
        it('url returns — when falsy', () => { expect(tpl().url(null, {})).toBe('—') })
        it('url returns url when set', () => { expect(tpl().url(null, { url: '/about' })).toBe('/about') })
        it('created_at delegates to formatDate', () => {
            expect(tpl().created_at(null, { created_at: '2024-01-01' })).toBe('2024-01-01')
        })
    })

    describe('requestAdapter', () => {
        const adapt = (d) => wrapper.vm.tableOptions.requestAdapter(d)
        it('defaults sort-field to created_at', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-field']).toBe('created_at') })
        it('sets asc when ascending=true', () => { expect(adapt({ ascending: true, query: '', page: 1, limit: 10 })['sort-order']).toBe('asc') })
        it('sets desc when ascending=false', () => { expect(adapt({ ascending: false, query: '', page: 1, limit: 10 })['sort-order']).toBe('desc') })
    })
})
