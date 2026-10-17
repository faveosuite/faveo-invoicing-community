jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ProductPluginMapping from '@/pages/admin/products/components/ProductPluginMapping.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const pluginData = {
    data: {
        data: {
            plugins: [
                { id: 1, name: 'Plugin A', is_bundled: true,  is_compatible: true  },
                { id: 2, name: 'Plugin B', is_bundled: false, is_compatible: true  },
                { id: 3, name: 'Plugin C', is_bundled: false, is_compatible: false },
            ],
        },
    },
}

describe('ProductPluginMapping.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/product\/1\/plugins/).reply(200, pluginData.data)
        globalThis.mockHttp.onPost(/\/product\/1\/plugins/).reply(200, { data: { message: 'Saved' } })
        wrapper = mount(ProductPluginMapping, {
            props: {
                productId: 1,
                baseUrl: '',
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['PluginFilter', 'v-client-table', 'action-button', 'loader', 'Tooltip'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches plugins on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/product\/1\/plugins/.test(r.url))).toBe(true)
    })

    it('populates allPlugins after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.allPlugins).toHaveLength(3)
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('calls errorHandler on fetch failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/product\/1\/plugins/).reply(500)
        wrapper = mount(ProductPluginMapping, {
            props: { productId: 1, baseUrl: '' },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['PluginFilter', 'v-client-table', 'action-button', 'loader', 'Tooltip'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('calls POST /product/:id/plugins on save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/product\/1\/plugins/.test(r.url))).toBe(true)
    })

    it('calls successHandler on successful save', async () => {
        await flushPromises()
        await wrapper.vm.save()
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on save failure', async () => {
        await flushPromises()
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/product\/1\/plugins/).reply(500)
        await wrapper.vm.save()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })
})
