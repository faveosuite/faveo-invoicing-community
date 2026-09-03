jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import CustomizeNotifications from '../../../../../Resources/js/Pages/ServerNotifications/CustomizeNotifications.vue'

const notificationFixture = { id: 1, notification_subject: 'Test Subject', notification_body: 'Test Body', notification_status: 1 }

describe('CustomizeNotifications.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        axiosMock.onGet('/api/admin/viewNotifications').reply(200, { data: [notificationFixture] })
        wrapper = mount(CustomizeNotifications, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert', 'form-field-template'],
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

    it('fetches notifications on mount', async () => {
        await flushPromises()
        expect(axiosMock.history.get[0].url).toContain('/api/admin/viewNotifications')
    })

    it('loading is false after data loads', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('handles fetch error gracefully', async () => {
        axiosMock.onGet('/api/admin/viewNotifications').reply(500, { message: 'Error' })
        wrapper = mount(CustomizeNotifications, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert', 'form-field-template'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls submit API on onSubmit', async () => {
        axiosMock.onPost(/\/api\/admin\/notifications\//).reply(200, { data: {}, message: 'Updated' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })

    it('onChange updates a notification field', async () => {
        await flushPromises()
        wrapper.vm.onChange('New message text', 'notification_product_not_found')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.notification_product_not_found).toBe('New message text')
    })

    it('onChange with falsy value sets empty string', async () => {
        await flushPromises()
        wrapper.vm.onChange('', 'notification_product_not_found')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.notification_product_not_found).toBe('')
    })

    it('onChange ignores unknown field', async () => {
        expect(() => wrapper.vm.onChange('value', 'unknown_field')).not.toThrow()
    })

    it('populates notification fields after fetch', async () => {
        axiosMock.onGet('/api/admin/viewNotifications').reply(200, {
            data: { id: 1, notification_product_not_found: 'Product not found', notification_product_inactive: 'Inactive' }
        })
        wrapper = mount(CustomizeNotifications, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert', 'form-field-template'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.notification_product_not_found).toBe('Product not found')
        expect(wrapper.vm.notification_id).toBe(1)
    })
})
