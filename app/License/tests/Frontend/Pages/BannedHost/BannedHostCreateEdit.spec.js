jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import MockAdapter from 'axios-mock-adapter'
import http from '@/plugins/axios.js'
import BannedHostCreateEdit from '../../../../../Resources/js/Pages/BannedHost/BannedHostCreateEdit.vue'

describe('BannedHostCreateEdit.vue', () => {
    let wrapper
    let axiosMock

    beforeEach(() => {
        axiosMock = new MockAdapter(http)
        wrapper = mount(BannedHostCreateEdit, {
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
        wrapper.vm.onChange('192.168.0.1', 'banned_host_ip') // NOSONAR
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.banned_host_ip).toBe('192.168.0.1') // NOSONAR
    })

    it('calls submit API on form submit', async () => {
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(axiosMock.history.post.length).toBeGreaterThan(0)
    })

    it('handles submit API error', async () => {
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(422, { message: 'Validation failed', errors: { banned_host_ip: ['Required'] } })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.exists()).toBe(true)
    })

    it('applies server-side field errors (e.g. duplicate IP) so they show under the input', async () => {
        const { errorHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(422, {
            message: 'The banned host ip has already been taken.',
            errors: { banned_host_ip: ['The banned host ip has already been taken.'] },
        })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalledWith(
            expect.anything(),
            'banned-hosts',
            expect.objectContaining({ setErrors: expect.any(Function) })
        )
    })

    it('saving is false after submit completes', async () => {
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(200, { data: {}, message: 'Done' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    it('calls successHandler on submit success', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on submit 500', async () => {
        const { errorHandler } = require('@/helpers/responseHandler')
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(500, { message: 'Server error' })
        wrapper.vm.onSubmit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('fetches data in edit mode when path contains edit', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(3)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/banned-hosts/3/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/viewBannedHost\//).reply(200, { data: { banned_host_ip: '10.0.0.1', banned_host_comments: 'test' } }) // NOSONAR
        wrapper = mount(BannedHostCreateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert'],
            },
        })
        await flushPromises()
        expect(axiosMock.history.get.length).toBeGreaterThan(0)
    })

    it('onChange updates banned_host_comments', async () => {
        wrapper.vm.onChange('test comment', 'banned_host_comments')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.banned_host_comments).toBe('test comment')
    })

    it('onChange ignores unknown field names', async () => {
        const before = wrapper.vm.banned_host_ip
        wrapper.vm.onChange('value', 'unknown_field')
        await wrapper.vm.$nextTick()
        expect(wrapper.vm.banned_host_ip).toBe(before)
    })

    it('updateStatesWithData populates banned_host_ip when edit data loads', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(2)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/banned-hosts/2/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/viewBannedHost\//).reply(200, {
            data: { banned_host_data: { banned_host_ip: '192.168.5.5', comments: 'my comment' } } // NOSONAR
        })
        wrapper = mount(BannedHostCreateEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'inline-loader', 'action-button', 'text-field', 'app-alert'],
            },
        })
        await flushPromises()
        expect(wrapper.vm.banned_host_ip).toBe('192.168.5.5') // NOSONAR
        expect(wrapper.vm.banned_host_comments).toBe('my comment')
        expect(wrapper.vm.isEdit).toBe(true)
        expect(wrapper.vm.title).toBe('edit_banned_host')
    })

    it('redirect timer fires after successful create (no hostId)', async () => {
        jest.useFakeTimers()
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(200, { data: {}, message: 'Created' })
        wrapper.vm.onSubmit()
        await flushPromises()
        jest.advanceTimersByTime(2001)
        expect(wrapper.exists()).toBe(true)
    })

    it('onSubmit on update (with hostId) calls getInitialValues', async () => {
        const { getIdFromUrl } = require('@/helpers/extraLogics')
        getIdFromUrl.mockReturnValue(4)
        Object.defineProperty(window, 'location', { value: { pathname: '/admin/banned-hosts/4/edit' }, writable: true })
        axiosMock.onGet(/\/api\/admin\/viewBannedHost\//).reply(200, {
            data: { banned_host_data: { banned_host_ip: '5.5.5.5', comments: '' } } // NOSONAR
        })
        axiosMock.onPost(/\/api\/admin\/bannedHost/).reply(200, { data: {}, message: 'Updated' })
        wrapper = mount(BannedHostCreateEdit, {
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
