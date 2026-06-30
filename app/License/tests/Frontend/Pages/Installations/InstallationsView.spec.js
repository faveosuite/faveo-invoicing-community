jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 1) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v, formatDate: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import InstallationsView from '../../../../../Resources/js/Pages/Installations/InstallationsView.vue'

const installationFixture = {
    id: 1, installation_domain: 'example.com', installation_ip: '1.2.3.4', // NOSONAR
    installation_status: 1, product_title: 'Product A',
    license_code: 'LIC-001', license_id: 5,
}

describe('InstallationsView.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet(/\/api\/admin\/installationView\//).reply(200, { data: installationFixture })
        wrapper = mount(InstallationsView, {
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

    it('renders the card structure', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('fetches installation data on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get.length).toBeGreaterThan(0)
        expect(axiosMock.history.get[0].url).toContain('/api/admin/installationView/')
    })

    it('loading is false after data loads', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('populates installation_domain after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.installation_domain).toBe('example.com')
    })

    it('populates installation_ip after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.installation_ip).toBe('1.2.3.4') // NOSONAR
    })

    it('populates product_title after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.product_title).toBe('Product A')
    })

    it('populates license_code after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.license_code).toBe('LIC-001')
    })

    it('handles fetch error gracefully', async () => {
        axiosMock.onGet(/\/api\/admin\/installationView\//).reply(500, { message: 'Error' })
        wrapper = mount(InstallationsView, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'table-actions', 'inline-loader', 'action-button'],
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

    it('onClose sets showModal to false', () => {
        wrapper.vm.showDeleteModal()
        wrapper.vm.onClose()
        expect(wrapper.vm.showModal).toBe(false)
    })

    it('onDeleted can be called without throwing', () => {
        expect(() => wrapper.vm.onDeleted()).not.toThrow()
    })

    // ── Callbacks tab (updateData) ────────────────────────────────────────────

    it('updateData sets endPoint for callbacks', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.endPoint).toContain('/api/admin/installationCallbacks/')
    })

    it('updateData sets columns for callbacks', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.columns).toContain('callback_status')
        expect(wrapper.vm.columns).toContain('callback_domain')
    })

    it('callback responseAdapter maps keyVal and idVal', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const rows = [{ id: 4, callback_ip: '1.2.3.4' }] // NOSONAR
        const result = wrapper.vm.tableOptions.responseAdapter({ data: { data: { data: rows, total: 1 } } })
        expect(result.data[0].keyVal).toBe('id')
        expect(result.data[0].idVal).toBe(4)
        expect(result.count).toBe(1)
    })

    it('callback template callback_ip returns em dash when null', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.tableOptions.templates.callback_ip(null, { callback_ip: null })).toBe('—')
    })

    it('callback template callback_status returns success badge', async () => {
        await flushPromises()
        wrapper.vm.updateData('callbacks', 1)
        await wrapper.vm.$nextTick()
        const result = wrapper.vm.tableOptions.templates.callback_status(null, { callback_status: 1 })
        expect(result.props.class).toContain('bg-success')
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
})
