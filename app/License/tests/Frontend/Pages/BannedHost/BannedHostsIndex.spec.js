jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import BannedHostsIndex from '../../../../../Resources/js/Pages/BannedHost/BannedHostsIndex.vue'

describe('BannedHostsIndex.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(BannedHostsIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'delete-modal'],
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
        expect(wrapper.find('.card-title').text()).toBe('view_banned_hosts')
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('initializes columns correctly', () => {
        expect(wrapper.vm.columns).toContain('banned_host_ip')
        expect(wrapper.vm.columns).toContain('actions')
    })

    it('endPoint contains viewBannedHost', () => {
        expect(wrapper.vm.endPoint).toContain('/api/admin/viewBannedHost')
    })

    it('initializes sortable options', () => {
        expect(wrapper.vm.options.sortable).toContain('banned_host_ip')
    })

    it('requestAdapter maps sort field correctly', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: 'banned_host_ip', ascending: true, query: '  test  ', limit: 10, page: 2 })
        expect(result.sort_field).toBe('banned_host_ip')
        expect(result.sort_order).toBe('asc')
        expect(result.perPage).toBe(10)
        expect(result.page).toBe(2)
    })

    it('requestAdapter falls back to id when orderBy is empty', () => {
        const result = wrapper.vm.options.requestAdapter({ orderBy: '', ascending: false, query: '', limit: 25, page: 1 })
        expect(result.sort_field).toBe('id')
        expect(result.sort_order).toBe('desc')
    })

    it('responseAdapter maps rows with edit_url, delete_url, keyVal, idVal', () => {
        const rows = [{ id: 1, banned_host_ip: '192.168.1.1', comments: 'test' }] // NOSONAR        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.count).toBe(1)
        expect(result.data[0].edit_url).toContain('/banned-hosts/1/edit')
        expect(result.data[0].delete_url).toContain('/api/admin/bannedHosts/delete')
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(1)
    })

    it('responseAdapter returns empty array for empty data', () => {
        const result = wrapper.vm.options.responseAdapter({ data: { data: { data: [], total: 0 } } })
        expect(result.data).toHaveLength(0)
        expect(result.count).toBe(0)
    })

    it('template banned_host_ip returns em dash when null', () => {
        expect(wrapper.vm.options.templates.banned_host_ip(null, { banned_host_ip: null })).toBe('—')
    })

    it('template banned_host_ip returns value when present', () => {
        expect(wrapper.vm.options.templates.banned_host_ip(null, { banned_host_ip: '10.0.0.1' })).toBe('10.0.0.1') // NOSONAR    })

    it('template comments returns em dash when null', () => {
        expect(wrapper.vm.options.templates.comments(null, { comments: null })).toBe('—')
    })

    it('columnsClasses defines correct classes', () => {
        expect(wrapper.vm.options.columnsClasses.banned_host_ip).toBe('dt-code')
        expect(wrapper.vm.options.columnsClasses.actions).toBe('dt-action')
    })

    it('template banned_host_date calls formatDate', () => {
        const result = wrapper.vm.options.templates.banned_host_date(null, { banned_host_date: '2024-01-01' })
        expect(result).toBe('2024-01-01')
    })

    it('headings defines expected labels', () => {
        expect(wrapper.vm.options.headings.banned_host_ip).toBe('ip_address')
        expect(wrapper.vm.options.headings.actions).toBe('actions')
    })
})
