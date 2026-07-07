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
    general_og_same_at_meta: 0,
    pages_title_format: '{name} | {company}',
    groups_title_format: '{name} | {company}',
    pages_description_format: '',
    groups_description_format: '',
    pages_og_title_format: '',
    groups_og_title_format: '',
    pages_og_description_format: '',
    groups_og_description_format: '',
    pages_og_image: '',
    groups_og_image: '',
    pages_og_same_as_meta: 0,
    groups_og_same_as_meta: 0,
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
        expect(wrapper.vm.settingsForm.pages_title_format).toBe('{name} | {company}')
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

    describe('tabs', () => {
        it('defaults to the general tab', () => {
            expect(wrapper.vm.tab).toBe('general')
        })

        it('shows the General tab link as active by default', () => {
            const links = wrapper.findAll('.nav-link')
            expect(links[0].classes()).toContain('active')
        })

        it('switches to the pages tab on click', async () => {
            const links = wrapper.findAll('.nav-link')
            await links[1].trigger('click')
            expect(wrapper.vm.tab).toBe('pages')
        })

        it('switches to the groups tab on click', async () => {
            const links = wrapper.findAll('.nav-link')
            await links[2].trigger('click')
            expect(wrapper.vm.tab).toBe('groups')
        })

        it('only shows the active tab’s save button section', async () => {
            await flushPromises()
            // three inner cards exist (one per tab), each with its own footer/button
            expect(wrapper.findAll('.card-footer').length).toBe(3)
        })
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

    describe('same-as-meta mirroring — pages', () => {
        it('mirrors pages_title_format/description into pages_og_* when turned on', () => {
            wrapper.vm.settingsForm.pages_title_format = 'Pages Title'
            wrapper.vm.settingsForm.pages_description_format = 'Pages Desc'
            wrapper.vm.onPagesOgSameAsMetaChange(true)
            expect(wrapper.vm.settingsForm.pages_og_title_format).toBe('Pages Title')
            expect(wrapper.vm.settingsForm.pages_og_description_format).toBe('Pages Desc')
        })

        it('keeps pages_og_title_format in sync while same-as-meta is on', async () => {
            wrapper.vm.onPagesOgSameAsMetaChange(true)
            wrapper.vm.settingsForm.pages_title_format = 'Updated Pages Title'
            await wrapper.vm.$nextTick()
            expect(wrapper.vm.settingsForm.pages_og_title_format).toBe('Updated Pages Title')
        })
    })

    describe('same-as-meta mirroring — groups', () => {
        it('mirrors groups_title_format/description into groups_og_* when turned on', () => {
            wrapper.vm.settingsForm.groups_title_format = 'Groups Title'
            wrapper.vm.settingsForm.groups_description_format = 'Groups Desc'
            wrapper.vm.onGroupsOgSameAsMetaChange(true)
            expect(wrapper.vm.settingsForm.groups_og_title_format).toBe('Groups Title')
            expect(wrapper.vm.settingsForm.groups_og_description_format).toBe('Groups Desc')
        })

        it('keeps groups_og_title_format in sync while same-as-meta is on', async () => {
            wrapper.vm.onGroupsOgSameAsMetaChange(true)
            wrapper.vm.settingsForm.groups_title_format = 'Updated Groups Title'
            await wrapper.vm.$nextTick()
            expect(wrapper.vm.settingsForm.groups_og_title_format).toBe('Updated Groups Title')
        })
    })

    describe('image change handlers', () => {
        it('onGeneralImageChange updates the general OG image preview', () => {
            wrapper.vm.onGeneralImageChange({ image: 'blob:general', file: new File([], 'g.png'), name: 'g.png' })
            expect(wrapper.vm.generalOgImagePreview).toBe('blob:general')
        })

        it('onPagesImageChange updates the pages OG image preview', () => {
            wrapper.vm.onPagesImageChange({ image: 'blob:pages', file: new File([], 'p.png'), name: 'p.png' })
            expect(wrapper.vm.pagesOgImagePreview).toBe('blob:pages')
        })

        it('onGroupsImageChange updates the groups OG image preview', () => {
            wrapper.vm.onGroupsImageChange({ image: 'blob:groups', file: new File([], 'gr.png'), name: 'gr.png' })
            expect(wrapper.vm.groupsOgImagePreview).toBe('blob:groups')
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
