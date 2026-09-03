jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: {}, query: {} }),
    RouterLink: { template: '<a><slot/></a>' },
}))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SeoSettingsIndex from '@/pages/admin/settings/settings/seo/SeoSettingsIndex.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

const settingsFixture = {
    favicon_title: 'Faveo Billing',
    favicon_title_client: 'Faveo Billing',
    general_description: 'General description',
    general_og_title: '',
    general_og_description: '',
    general_og_image: '',
    general_og_same_as_meta: 0,
}

const mountPage = () =>
    mount(SeoSettingsIndex, {
        global: {
            plugins: [createTestingPinia()],
            stubs: ['AppAlert', 'SeoMetaField', 'ImageUpload', 'Checkbox', 'action-button', 'loader'],
        },
    })

describe('SeoSettingsIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/seo\/settings/).reply(200, { data: settingsFixture })
        globalThis.mockHttp.onPost(/\/seo\/settings/).reply(200, { data: { message: 'Updated' } })
        wrapper = mountPage()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('GETs /seo/settings on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/seo\/settings/.test(r.url))).toBe(true)
    })

    it('populates settingsForm from the fetched data', async () => {
        await flushPromises()
        expect(wrapper.vm.settingsForm.favicon_title).toBe('Faveo Billing')
        expect(wrapper.vm.settingsForm.general_description).toBe('General description')
    })

    it('sets loadingSettings to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loadingSettings).toBe(false)
    })

    it('calls errorHandler when the fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/seo\/settings/).reply(500)
        wrapper = mountPage()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    describe('same-as-meta mirroring — general', () => {
        it('mirrors favicon_title_client/general_description into general_og_title/description when turned on', () => {
            wrapper.vm.settingsForm.favicon_title_client = 'Client Title'
            wrapper.vm.settingsForm.general_description = 'General desc'
            wrapper.vm.onGeneralOgSameAsMetaChange(true)
            expect(wrapper.vm.settingsForm.general_og_title).toBe('Client Title')
            expect(wrapper.vm.settingsForm.general_og_description).toBe('General desc')
        })

        it('keeps general_og_title in sync while same-as-meta is on', async () => {
            wrapper.vm.onGeneralOgSameAsMetaChange(true)
            wrapper.vm.settingsForm.favicon_title_client = 'Updated Client Title'
            await wrapper.vm.$nextTick()
            expect(wrapper.vm.settingsForm.general_og_title).toBe('Updated Client Title')
        })

        it('does not sync when same-as-meta is off', async () => {
            wrapper.vm.onGeneralOgSameAsMetaChange(false)
            wrapper.vm.settingsForm.favicon_title_client = 'Should not sync'
            await wrapper.vm.$nextTick()
            expect(wrapper.vm.settingsForm.general_og_title).not.toBe('Should not sync')
        })
    })

    describe('image change handlers', () => {
        it('onGeneralImageChange updates the general OG image preview', () => {
            wrapper.vm.onGeneralImageChange({ image: 'blob:general', file: new File([], 'g.png'), name: 'g.png' })
            expect(wrapper.vm.generalOgImagePreview).toBe('blob:general')
        })
    })

    describe('saveSettings', () => {
        it('POSTs the current settingsForm fields to /seo/settings', async () => {
            await flushPromises()
            wrapper.vm.settingsForm.favicon_title = 'New Admin Title'
            await wrapper.vm.saveSettings()
            await flushPromises()
            const call = globalThis.mockHttp.history.post.find(r => /\/seo\/settings/.test(r.url))
            expect(call).toBeTruthy()
            expect(call.data.get('favicon_title')).toBe('New Admin Title')
        })

        it('does not use a _method override (this endpoint is a real POST route)', async () => {
            await flushPromises()
            await wrapper.vm.saveSettings()
            await flushPromises()
            const call = globalThis.mockHttp.history.post.find(r => /\/seo\/settings/.test(r.url))
            expect(call.data.get('_method')).toBeNull()
        })

        it('calls successHandler on success', async () => {
            await flushPromises()
            await wrapper.vm.saveSettings()
            await flushPromises()
            expect(successHandler).toHaveBeenCalled()
        })

        it('calls errorHandler on failure', async () => {
            await flushPromises()
            globalThis.mockHttp.reset()
            globalThis.mockHttp.onPost(/\/seo\/settings/).reply(500)
            await wrapper.vm.saveSettings()
            await flushPromises()
            expect(errorHandler).toHaveBeenCalled()
        })

        it('includes any selected OG images in the payload', async () => {
            await flushPromises()
            wrapper.vm.onGeneralImageChange({ image: 'blob:g', file: new File(['x'], 'g.png'), name: 'g.png' })
            await wrapper.vm.saveSettings()
            await flushPromises()
            const call = globalThis.mockHttp.history.post.find(r => /\/seo\/settings/.test(r.url))
            expect(call.data.get('general_og_image')).toBeTruthy()
        })

        it('sets savingSettings back to false after completion', async () => {
            await flushPromises()
            await wrapper.vm.saveSettings()
            await flushPromises()
            expect(wrapper.vm.savingSettings).toBe(false)
        })
    })
})
