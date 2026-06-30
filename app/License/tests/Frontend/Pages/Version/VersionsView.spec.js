jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 1) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import VersionsView from '../../../../../Resources/js/Pages/Version/VersionsView.vue'

const versionFixture = {
    id: 1, version_number: '2.0.0', version_status: 1,
    product_title: 'Product A', product_id: 3,
    version_date: '2024-01-01', version_install_count: 5,
}

describe('VersionsView.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet(/\/api\/admin\/versionView\//).reply(200, { data: versionFixture })
        wrapper = mount(VersionsView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'action-button'],
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
        expect(wrapper.find('.card-title').exists()).toBe(true)
    })

    it('fetches version data on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.length).toBeGreaterThan(0)
        expect(axiosMock.history.get[0].url).toContain('/api/admin/versionView/')
    })

    it('loading is false after data loads', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('populates product_title after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.product_title).toBe('Product A')
    })

    it('populates version_status after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.version_status).toBe(1)
    })

    it('populates version_install_count after successful fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.version_install_count).toBe(5)
    })

    // ── Callbacks tab (updateData) ────────────────────────────────────────────

    it('updateData sets endPoint for callbacks', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.endPoint).toContain('/api/admin/versionCallbacks/')
    })

    it('updateData sets callback columns', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.columns).toContain('callback_status')
        expect(wrapper.vm.columns).toContain('callback_ip')
    })

    it('callback responseAdapter maps keyVal as version_id', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        const rows = [{ version_id: 2, callback_ip: '1.2.3.4' }] // NOSONAR        const result = wrapper.vm.tableOptions.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.data[0].keyVal).toBe('version_id')
        expect(result.data[0].idVal).toBe(2)
    })

    it('callback requestAdapter maps correctly', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.requestAdapter({ orderBy: '', ascending: true, query: '', limit: 10 })
        expect(result.sort_field).toBe('id')
    })

    it('callback template callback_status returns success badge', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 1 })
        expect(result.props.class).toContain('bg-success')
    })

    it('callback template callback_status returns danger badge', async () => {
        await flushPromises()
        wrapper.vm.updateData(1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 0 })
        expect(result.props.class).toContain('bg-danger')
    })

    it('handles API error gracefully', async () => {
        axiosMock.onGet(/\/api\/admin\/versionView\//).reply(500, { message: 'Server error' })
        wrapper = mount(VersionsView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'action-button'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })
})
