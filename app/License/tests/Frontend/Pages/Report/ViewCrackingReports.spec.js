jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import ViewCrackingReports from '../../../../../Resources/js/Pages/Report/ViewCrackingReports.vue'

describe('ViewCrackingReports.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(ViewCrackingReports, {
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
        expect(wrapper.find('.card-title').text()).toBe('view_cracking_reports')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toEqual(['report_text', 'license_code', 'report_date_time', 'report_status'])
    })

    it('initializes sortable options', () => {
        expect(wrapper.vm.options.sortable).toEqual(['report_text', 'license_code', 'report_date_time', 'report_status'])
    })

    it('initializes filterable options', () => {
        expect(wrapper.vm.options.filterable).toEqual(['report_text'])
    })

    it('endPoint contains reportCracking', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/reportCracking')
    })
})
