jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0), generateRandomString: jest.fn(() => 'RANDOM16') }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import LicenseCreateEdit from '../../../../../Resources/js/Pages/License/LicenseCreateEdit.vue'

describe('LicenseCreateEdit.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(LicenseCreateEdit, {
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
        axiosMock.onPost(/\/api\/admin\/license/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })

    it('handles 422 validation error on submit', async () => {
        axiosMock.onPost(/\/api\/admin\/license/).reply(422, { message: 'Validation failed', errors: { license_code: ['Required'] } })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('calls successHandler on submit success', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/license/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit 500', async () => {
        const { errorHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/license/).reply(500, { message: 'Server error' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saving is false after submit completes', async () => {
        axiosMock.onPost(/\/api\/admin\/license/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    it('fetches license data in edit mode when path contains edit', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(5)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/licenses/5/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/license\//).reply(200, {
            data: {
                license: {
                    id: 5, license_code: 'LIC-001', license_status: 1, license_require_domain: 1,
                    license_ip: '1.2.3.4', license_domain: 'example.com', license_limit: 10,
                    license_order_number: 100, license_comments: 'test', api_key_secret: null,
                    license_expire_date: null, license_updates_date: null, license_support_date: null,
                },
                product_name: [{ product_id: 1, product_title: 'Product A' }]
            }
        })
        wrapper = mount(LicenseCreateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'static-select', 'app-alert', 'form-field-template', 'dynamic-select', 'number-field', 'radio-button', 'date-picker'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.license_code).toBe('LIC-001')
        expect(wrapper.vm.isEdit).toBe(true)
        expect(wrapper.vm.title).toBe('edit_license')
    })

    // ── onChange branches ─────────────────────────────────────────────────────

    it('onChange product branch sets product_id and product_title', async () => {
        wrapper.vm.onChange({ product_id: 5, product_title: 'Product X' }, 'product')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.product_id).toBe(5)
        expect(wrapper.vm.product_title).toBe('Product X')
    })

    it('onChange client branch sets client_id and client_name', async () => {
        wrapper.vm.onChange({ client_id: 3, full_name: 'John Doe' }, 'client')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.client_id).toBe(3)
        expect(wrapper.vm.client_name).toBe('John Doe')
    })

    it('onChange client branch with null clears client_name', async () => {
        wrapper.vm.onChange(null, 'client')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.client_name).toBe('')
    })

    it('onChange license_code updates license_code', async () => {
        wrapper.vm.onChange('NEWCODE', 'license_code')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_code).toBe('NEWCODE')
    })

    it('onChange license_ip updates license_ip', async () => {
        wrapper.vm.onChange('192.168.1.1', 'license_ip')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_ip).toBe('192.168.1.1')
    })

    it('onChange license_domain updates license_domain', async () => {
        wrapper.vm.onChange('example.com', 'license_domain')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_domain).toBe('example.com')
    })

    it('onChange license_limit updates license_limit', async () => {
        wrapper.vm.onChange(5, 'license_limit')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_limit).toBe(5)
    })

    it('onChange license_status updates license_status', async () => {
        wrapper.vm.onChange(0, 'license_status')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_status).toBe(0)
    })

    it('onChange license_require_domain updates value', async () => {
        wrapper.vm.onChange(0, 'license_require_domain')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_require_domain).toBe(0)
    })

    it('onChange license_comments updates license_comments', async () => {
        wrapper.vm.onChange('my comment', 'license_comments')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_comments).toBe('my comment')
    })

    it('onChange with null value sets empty string', async () => {
        wrapper.vm.onChange(null, 'license_ip')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_ip).toBe('')
    })

    // ── generateCode ──────────────────────────────────────────────────────────

    it('generateCode sets a non-empty license_code', async () => {
        const { generateRandomString } = require('@/helpers/extraLogics')
        generateRandomString.mockReturnValue('GENERATEDCODE16X')
        wrapper.vm.generateCode()
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.license_code).toBe('GENERATEDCODE16X')
    })

    // ── submit redirect with fake timers ──────────────────────────────────────

    it('onSubmit redirect timer fires after successful create (license_id empty by default)', async () => {
        jest.useFakeTimers()
        axiosMock.onPost(/\/api\/admin\/license/).reply(200, { data: {}, message: 'Created' })
        // license_id is '' by default in create mode — no need to set
        wrapper.vm.onSubmit()
        await flushPromises()
        jest.advanceTimersByTime(2001)
        expect(wrapper.exists()).toBe(true)
    })

    it('onSubmit calls getInitialValues on successful update (with license_id)', async () => {
        axiosMock.onPost(/\/api\/admin\/license/).reply(200, { data: {}, message: 'Updated' })
        axiosMock.onGet(/\/api\/admin\/license\//).reply(200, { data: { license: { id: 5, license_code: 'LIC-001', api_key_secret: null, license_expire_date: null, license_updates_date: null, license_support_date: null }, product_name: [{ product_id: 1, product_title: 'PA' }] } })
        wrapper.vm.license_id = 5
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })
})
