jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/helpers/luxonHelpers', () => ({ phpToLuxon: jest.fn((fmt) => fmt) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SystemSettings from '@/pages/admin/settings/settings/SystemSettings.vue'

describe('SystemSettings.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/settings\/system-data/).reply(200, {
            data: {
                timezones: [{ id: 1, location: 'UTC' }],
                date_formats: [{ value: 'Y-m-d', label: '2024-01-01' }],
                time_formats: [{ value: 'H:i', label: '14:30' }],
                settings: { timezone_id: 1, date_format: 'Y-m-d', time_format: 'H:i' },
            },
        })
        globalThis.mockHttp.onPost(/\/settings\/datetime-data/).reply(200, { data: {} })
        wrapper = mount(SystemSettings, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'loader', 'SelectField', 'action-button'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches system data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/settings\/system-data/)
    })

    it('calls POST datetime-data on save', async () => {
        await flushPromises()
        wrapper.vm.form.timezone_id = { id: 1, name: 'UTC' }
        wrapper.vm.form.date_format = { id: 'Y-m-d', name: '2024-01-01' }
        wrapper.vm.form.time_format = { id: 'H:i', name: '14:30' }
        wrapper.vm.save()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('settings/datetime-data'))).toBeTruthy()
    })

    it('calls successHandler after successful save', async () => {
        const { successHandler } = require('@/helpers/responseHandler')
        await flushPromises()
        wrapper.vm.form.timezone_id = { id: 1, name: 'UTC' }
        wrapper.vm.form.date_format = { id: 'Y-m-d', name: '2024-01-01' }
        wrapper.vm.form.time_format = { id: 'H:i', name: '14:30' }
        wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })
})
