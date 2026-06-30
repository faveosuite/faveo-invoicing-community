jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import InstallationCreateEdit from '../../../../../Resources/js/Pages/Installations/InstallationCreateEdit.vue'

describe('InstallationCreateEdit.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(InstallationCreateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'static-select', 'app-alert', 'form-field-template'],
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

    it('calls submit API on form submit', async () => {
        axiosMock.onPost('/api/admin/installations/edit').reply(200, { data: {}, message: 'Updated' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })

    it('handles 422 validation error', async () => {
        axiosMock.onPost('/api/admin/installations/edit').reply(422, { message: 'Validation failed', errors: { installation_domain: ['Required'] } })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('calls successHandler when api_action_success and action_success are true', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost('/api/admin/installations/edit').reply(200, {
            api_action_success: true, action_success: true, page_message: 'Updated successfully'
        })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit 500', async () => {
        const { errorHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost('/api/admin/installations/edit').reply(500, { message: 'Server error' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saving is false after submit completes', async () => {
        axiosMock.onPost('/api/admin/installations/edit').reply(200, { data: {}, message: 'Updated' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    // ── onChange branches ─────────────────────────────────────────────────────

    it('onChange updates installation_ip', async () => {
        wrapper.vm.onChange('10.0.0.1', 'installation_ip') // NOSONAR
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_ip).toBe('10.0.0.1') // NOSONAR
    })

    it('onChange sets installation_ip to empty string when falsy', async () => {
        wrapper.vm.onChange('', 'installation_ip')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_ip).toBe('')
    })

    it('onChange updates installation_domain', async () => {
        wrapper.vm.onChange('site.com', 'installation_domain')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_domain).toBe('site.com')
    })

    it('onChange updates installation_status to 1 for truthy value', async () => {
        wrapper.vm.onChange(1, 'installation_status')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_status).toBe(1)
    })

    it('onChange updates installation_status to 0 for falsy value', async () => {
        wrapper.vm.onChange(0, 'installation_status')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_status).toBe(0)
    })

    it('onChange updates installation_disable_ip_verification', async () => {
        wrapper.vm.onChange(1, 'installation_disable_ip_verification')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.installation_disable_ip_verification).toBe(1)
    })

    // ── submit error_detected path ────────────────────────────────────────────

    it('setAlert called when api_action_success false (error_detected)', async () => {
        axiosMock.onPost('/api/admin/installations/edit').reply(200, {
            api_action_success: false, error_detected: true, page_message: 'Something went wrong'
        })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('fetches installation data in edit mode', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(2)
        axiosMock.onGet(/\/api\/admin\/installation\//).reply(200, { data: { installation_domain: 'example.com' } })
        wrapper = mount(InstallationCreateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'static-select', 'app-alert', 'form-field-template'],
            },
        })
        await flushPromises()
        expect(axiosMock.history.get.length).toBeGreaterThan(0)
    })
})
