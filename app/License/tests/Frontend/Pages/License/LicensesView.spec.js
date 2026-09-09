jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 1) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import LicensesView from '../../../../../Resources/js/Pages/License/LicensesView.vue'

const licenseFixture = {
    id: 1, license_code: 'LIC-001', license_status: 1,
    client_email: 'test@example.com', client_id: 5,
    product_title: 'Product A', product_id: 2,
    license_domain: 'example.com', license_ip: '1.2.3.4', // NOSONAR
    installation_counts: 3, license_expire_date: '2025-12-31',
}

describe('LicensesView.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet(/\/api\/admin\/licenseView\//).reply(200, { data: licenseFixture })
        wrapper = mount(LicensesView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'action-button', 'delete-modal'],
            },
        })
    })

    afterEach(() => {
        axiosMock.restore()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the card structure', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('fetches license data on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.length).toBeGreaterThan(0)
        expect(axiosMock.history.get[0].url).toContain('/api/admin/licenseView/')
    })

    it('loading is false after data loads', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('populates license_code after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.license_code).toBe('LIC-001')
    })

    it('populates client_email after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.client_email).toBe('test@example.com')
    })

    it('populates product_title after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.product_title).toBe('Product A')
    })

    it('populates installation_counts after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.installation_counts).toBe(3)
    })

    it('handles fetch error gracefully', async () => {
        axiosMock.onGet(/\/api\/admin\/licenseView\//).reply(500, { message: 'Error' })
        wrapper = mount(LicensesView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'action-button', 'delete-modal'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    // ── Delete modal ──────────────────────────────────────────────────────────

    it('showDeleteModal toggles showModal to true', () => {
        expect(wrapper.vm.showModal).toBe(false)
        wrapper.vm.showDeleteModal()
        expect(wrapper.vm.showModal).toBe(true)
    })

    it('showDeleteModal toggles showModal off when called twice', () => {
        wrapper.vm.showDeleteModal()
        wrapper.vm.showDeleteModal()
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('onClose sets showModal to false', () => {
        wrapper.vm.showDeleteModal()
        expect(wrapper.vm.showModal).toBe(true)
        wrapper.vm.onClose()
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('onDeleted can be called without throwing', () => {
        expect(() => wrapper.vm.onDeleted()).not.toThrow()
    })

    // ── Tab switching ─────────────────────────────────────────────────────────

    it('activeTab defaults to installations', () => {
        expect(wrapper.vm.activeTab).toBe('installations')
    })

    it('updateData switches activeTab to callbacks', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('callbacks')
    })

    it('updateData switches activeTab to logs', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('logs')
    })

    it('updateData switches activeTab back to installations', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('installations')
    })

    // ── buildInstallationOptions ──────────────────────────────────────────────

    it('buildInstallationOptions returns sortable with installation_domain', async () => {
        await flushPromises()
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.sortable).toContain('installation_domain')
    })

    it('installation responseAdapter maps edit_url, view_url, keyVal', async () => {
        await flushPromises()
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        const rows = [{ id: 3, installation_domain: 'site.com' }]
        const result = wrapper.vm.tableOptions.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.data[0].edit_url).toContain('/installations/3/edit')
        expect(result.data[0].view_url).toContain('/installations/3/view')
        expect(result.data[0].keyVal).toBe('id')
    })

    it('installation template installation_ip returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.installation_ip(null, { installation_ip: null })).toBe('—')
    })

    it('installation template installation_status returns success badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.installation_status(null, { installation_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('installation template installation_status returns danger badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('installations', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.installation_status(null, { installation_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    // ── buildCallbackOptions ──────────────────────────────────────────────────

    it('buildCallbackOptions returns sortable with callback_status', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.sortable).toContain('callback_status')
    })

    it('callback responseAdapter maps keyVal and idVal', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const rows = [{ id: 7, callback_type: 'update' }]
        const result = wrapper.vm.tableOptions.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(7)
        expect(result.count).toBe(1)
    })

    it('callback template callback_ip returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.callback_ip(null, { callback_ip: null })).toBe('—')
    })

    it('callback template callback_ip returns value when present', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.callback_ip(null, { callback_ip: '1.2.3.4' })).toBe('1.2.3.4') // NOSONAR
    })

    it('callback template callback_domain returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.callback_domain(null, { callback_domain: null })).toBe('—')
    })

    it('callback template callback_domain returns anchor when present', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_domain(null, { callback_domain: 'example.com' })
        expect(result.props.href).toContain('example.com')
    })

    it('callback template callback_status returns success badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('callback template callback_status returns danger badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('callback requestAdapter falls back to id when orderBy empty', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.requestAdapter({ orderBy: '', ascending: true, query: '', limit: 10 })
        expect(result.sort_field).toBe('id')
    })

    // ── buildLogsOptions (logs tab) ───────────────────────────────────────────

    it('updateData logs tab sets activeTab to logs', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.activeTab).toBe('logs')
    })

    it('logs tab endPoint contains installationLogs', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.endPoint).toContain('/api/admin/installationLogs/')
    })

    it('logs tab sortable contains installation_domain', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.sortable).toContain('installation_domain')
        expect(wrapper.vm.tableOptions.sortable).toContain('installation_status')
    })

    it('logs requestAdapter maps with fallback to installation_last_active_date', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.requestAdapter({ orderBy: '', ascending: false, query: '  q  ', limit: 10 })
        expect(result.sort_field).toBe('installation_last_active_date')
        expect(result.sort_order).toBe('asc')
        expect(result.search_query).toBe('q')
    })

    it('logs requestAdapter uses provided orderBy', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.requestAdapter({ orderBy: 'installation_domain', ascending: true, query: '', limit: 25 })
        expect(result.sort_field).toBe('installation_domain')
        expect(result.sort_order).toBe('desc')
    })

    it('logs responseAdapter maps keyVal and count', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const rows = [{ id: 9, installation_domain: 'log.com' }]
        const result = wrapper.vm.tableOptions.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(9)
        expect(result.count).toBe(1)
    })

    it('logs template installation_ip returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.installation_ip(null, { installation_ip: null })).toBe('—')
    })

    it('logs template installation_ip returns value when present', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.installation_ip(null, { installation_ip: '5.5.5.5' })).toBe('5.5.5.5') // NOSONAR
    })

    it('logs template version_number returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.version_number(null, { version_number: null })).toBe('—')
    })

    it('logs template version_number returns value when present', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.version_number(null, { version_number: '2.0.0' })).toBe('2.0.0')
    })

    it('logs template installation_domain returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.installation_domain(null, { installation_domain: null })).toBe('—')
    })

    it('logs template installation_domain returns anchor when present', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.installation_domain(null, { installation_domain: 'log.com' })
        expect(result.props.href).toContain('log.com')
    })

    it('logs template installation_status returns success badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.installation_status(null, { installation_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('logs template installation_status returns danger badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('logs', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.installation_status(null, { installation_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    // ── copyCommand ───────────────────────────────────────────────────────────

    it('copyCommand sets copied to true then resets after timer', async () => {
        jest.useFakeTimers()
        Object.assign(navigator, { clipboard: { writeText: jest.fn() } })
        await flushPromises()
        wrapper.vm.copyCommand()
        expect(wrapper.vm.copied).toBe(true)
        jest.advanceTimersByTime(2001)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.copied).toBe(false)
    })
})
