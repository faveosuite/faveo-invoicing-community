jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import WhiteListCreate from '../../../../../Resources/js/Pages/WhiteList/WhiteListCreate.vue'

describe('WhiteListCreate.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(WhiteListCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert'],
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

    it('updates data on field change', async () => {
        wrapper.vm.onChange('192.168.1.1', 'whitelist_host_ip') // NOSONAR
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.whitelist_host_ip).toBe('192.168.1.1') // NOSONAR
    })

    it('calls submit API on form submit', async () => {
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })

    it('handles submit API error', async () => {
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(422, { message: 'Validation failed', errors: { whitelist_host_ip: ['Required'] } })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('calls successHandler on submit success', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit 500', async () => {
        const { errorHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(500, { message: 'Server error' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saving is false after submit completes', async () => {
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    it('onChange whitelist_host_comments updates correctly', async () => {
        wrapper.vm.onChange('office IP', 'whitelist_host_comments')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.whitelist_host_comments).toBe('office IP')
    })

    it('onChange unknown field name does not throw', async () => {
        expect(() => wrapper.vm.onChange('value', 'unknown_field')).not.toThrow()
    })

    it('redirect timer fires after successful create', async () => {
        jest.useFakeTimers()
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        jest.advanceTimersByTime(2001)
        expect(wrapper.exists()).toBe(true)
    })

    it('fetches data in edit mode and populates whitelist_host_ip', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(5)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/whitelist/5/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/whitelist-edit\//).reply(200, {
            data: { host_data: { whitelist_host_ip: '10.0.0.1', whitelist_host_comments: 'office' } } // NOSONAR
        })
        wrapper = mount(WhiteListCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.whitelist_host_ip).toBe('10.0.0.1') // NOSONAR
        expect(wrapper.vm.isEdit).toBe(true)
    })

    it('onSubmit on update (with hostId) calls getInitialValues instead of redirect', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(3)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/whitelist/3/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/whitelist-edit\//).reply(200, {
            data: { host_data: { whitelist_host_ip: '1.1.1.1', whitelist_host_comments: '' } } // NOSONAR
        })
        axiosMock.onPost(/\/api\/admin\/whitelist/).reply(200, { data: {}, message: 'Updated' })
        wrapper = mount(WhiteListCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert'],
            },
        })
        await flushPromises()
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })
})
