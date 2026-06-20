jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import ViewUpdateReports from '../../../../../Resources/js/Pages/Report/ViewUpdateReports.vue'

describe('ViewUpdateReports.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(ViewUpdateReports, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card title', () => {
        expect(wrapper.find('.card-title').text()).toBe('view_update_reports')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toEqual(['report_text', 'product', 'report_date_time', 'report_status'])
    })

    it('initializes sortable options', () => {
        expect(wrapper.vm.options.sortable).toContain('report_text')
        expect(wrapper.vm.options.sortable).toContain('report_date_time')
    })

    it('initializes filterable options', () => {
        expect(wrapper.vm.options.filterable).toEqual(['report_text'])
    })

    it('endPoint contains reportUpdate', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/reportUpdate')
    })

    it('requestAdapter falls back to report_date_time when orderBy empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '', limit: 10, page: 1 })
        expect(result.sort_field).toBe('report_date_time')
        expect(result.sort_order).toBe('desc')
    })

    it('requestAdapter maps correctly with query trim', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'report_text', ascending: true, query: '  update  ', limit: 25, page: 3 })
        expect(result.sort_field).toBe('report_text')
        expect(result.search_query).toBe('update')
        expect(result.page).toBe(3)
    })

    it('responseAdapter maps rows with keyVal and idVal', () => {
        const rows = [{ id: 8, report_text: 'update check' }]
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(8)
    })
})
