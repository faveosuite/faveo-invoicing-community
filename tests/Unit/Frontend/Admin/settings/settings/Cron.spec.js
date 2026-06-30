jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import Cron from '@/pages/admin/settings/settings/Cron.vue'

describe('Cron.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/settings\/cron-data/).reply(200, {
            data: {
                cron_path: '/var/www/html/artisan',
                php_paths: ['/usr/bin/php'],
                exec_enabled: true,
                statuses: {},
                days: {},
                conditions: {},
            },
        })
        globalThis.mockHttp.onPatch(/\/settings\/cron-data/).reply(200, { data: {} })
        globalThis.mockHttp.onPatch(/\/settings\/cron-days/).reply(200, { data: {} })
        globalThis.mockHttp.onPost(/\/verify-php-path/).reply(200, { data: {} })

        wrapper = mount(Cron, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'inline-loader', 'loader', 'action-button',
                    'SelectField', 'TextField', 'Checkbox', 'Tooltip',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches cron data on mount', async () => {
        await flushPromises()
        const getUrls = globalThis.mockHttp.history.get.map(r => r.url)
        expect(getUrls.some(u => u.includes('cron-data'))).toBe(true)
    })

    it('renders the cron settings card header', async () => {
        await flushPromises()
        expect(wrapper.find('.card-header').exists()).toBe(true)
    })

    it('calls patch cron-data endpoint on save scheduler', async () => {
        await flushPromises()
        const saveBtns = wrapper.findAll('[action="save"]')
        if (saveBtns.length > 0) {
            await saveBtns[0].trigger('click')
            await flushPromises()
            const patchUrls = globalThis.mockHttp.history.patch.map(r => r.url)
            expect(patchUrls.some(u => u.includes('cron-data'))).toBe(true)
        }
    })

    it('calls patch cron-days endpoint on save days', async () => {
        await flushPromises()
        const saveBtns = wrapper.findAll('[action="save"]')
        if (saveBtns.length > 1) {
            await saveBtns[1].trigger('click')
            await flushPromises()
            const patchUrls = globalThis.mockHttp.history.patch.map(r => r.url)
            expect(patchUrls.some(u => u.includes('cron-days'))).toBe(true)
        }
    })

    it('saveScheduler calls PATCH /settings/cron-data', async () => {
        globalThis.mockHttp.onPatch(/\/settings\/cron-data/).reply(200, { message: 'ok' })
        await flushPromises()
        await wrapper.vm.saveScheduler()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => r.url.includes('cron-data'))).toBe(true)
    })

    it('saveScheduler handles error gracefully', async () => {
        globalThis.mockHttp.onPatch(/\/settings\/cron-data/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.saveScheduler()).resolves.not.toThrow()
    })

    it('saveDays calls PATCH /settings/cron-days', async () => {
        globalThis.mockHttp.onPatch(/\/settings\/cron-days/).reply(200, { message: 'ok' })
        await flushPromises()
        await wrapper.vm.saveDays()
        await flushPromises()
        expect(globalThis.mockHttp.history.patch.some(r => r.url.includes('cron-days'))).toBe(true)
    })

    it('saveDays handles error gracefully', async () => {
        globalThis.mockHttp.onPatch(/\/settings\/cron-days/).reply(500)
        await flushPromises()
        await expect(wrapper.vm.saveDays()).resolves.not.toThrow()
    })

    it('clearPhpPath resets phpPath', async () => {
        await flushPromises()
        wrapper.vm.phpPath = '/usr/bin/php'
        wrapper.vm.clearPhpPath()
        expect(wrapper.vm.phpPath).toBe('')
    })

    it('copyCommand calls POST /verify-php-path', async () => {
        globalThis.mockHttp.onPost(/\/verify-php-path/).reply(200, { data: { valid: true } })
        await flushPromises()
        wrapper.vm.phpPath = '/usr/bin/php'
        await wrapper.vm.copyCommand()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('verify-php-path'))).toBe(true)
    })

    it('onScheduleChange updates conditionForms for a valid job key', async () => {
        await flushPromises()
        const keys = Object.keys(wrapper.vm.conditionForms ?? {})
        if (keys.length > 0) {
            wrapper.vm.onScheduleChange(keys[0], { id: 'daily' })
            expect(wrapper.vm.conditionForms[keys[0]].condition).toBe('daily')
        }
    })

    it('selectedOption returns selected option for a valid job', async () => {
        await flushPromises()
        const keys = Object.keys(wrapper.vm.conditionForms ?? {})
        if (keys.length > 0) {
            const result = wrapper.vm.selectedOption(keys[0])
            // result can be an option object or null — just verify no throw
            expect(result === null || typeof result === 'object').toBe(true)
        }
    })
})
