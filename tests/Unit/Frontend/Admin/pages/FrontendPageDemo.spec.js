jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import FrontendPageDemo from '@/pages/admin/pages/FrontendPageDemo.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('FrontendPageDemo.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/demo/).reply(200, { data: { status: true } })
        global.mockHttp.onPost(/\/save\/demo/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(FrontendPageDemo, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'Checkbox', 'action-button', 'inline-loader', 'loader'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders AppAlert stub', () => {
        expect(wrapper.find('app-alert-stub').exists()).toBe(true)
    })

    it('renders the card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('GETs /demo on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /\/demo/.test(r.url))).toBe(true)
    })

    it('sets status from API response on mount', async () => {
        await flushPromises()
        expect(wrapper.vm.status).toBe(true)
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler when GET /demo fails', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/demo/).reply(500)
        wrapper = mount(FrontendPageDemo, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'Checkbox', 'action-button', 'inline-loader', 'loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('POSTs to /save/demo on save()', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => /\/save\/demo/.test(r.url))).toBe(true)
    })

    it('sends current status value in POST body', async () => {
        await flushPromises()
        wrapper.vm.status = false
        await wrapper.vm.save()
        await flushPromises()
        const body = JSON.parse(global.mockHttp.history.post[0].data)
        expect(body.status).toBe(false)
    })

    it('calls successHandler on successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        await flushPromises()
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/save\/demo/).reply(500)
        await wrapper.vm.save()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('saving resets to false after save completes', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})
