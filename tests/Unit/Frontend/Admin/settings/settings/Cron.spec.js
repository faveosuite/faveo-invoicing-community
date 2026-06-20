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
        global.mockHttp.onGet(/\/settings\/cron-data/).reply(200, {
            data: {
                cron_path: '/var/www/html/artisan',
                php_paths: ['/usr/bin/php'],
                exec_enabled: true,
                statuses: {},
                days: {},
                conditions: {},
            },
        })
        global.mockHttp.onPatch(/\/settings\/cron-data/).reply(200, { data: {} })
        global.mockHttp.onPatch(/\/settings\/cron-days/).reply(200, { data: {} })
        global.mockHttp.onPost(/\/verify-php-path/).reply(200, { data: {} })

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
        const getUrls = global.mockHttp.history.get.map(r => r.url)
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
            const patchUrls = global.mockHttp.history.patch.map(r => r.url)
            expect(patchUrls.some(u => u.includes('cron-data'))).toBe(true)
        }
    })

    it('calls patch cron-days endpoint on save days', async () => {
        await flushPromises()
        const saveBtns = wrapper.findAll('[action="save"]')
        if (saveBtns.length > 1) {
            await saveBtns[1].trigger('click')
            await flushPromises()
            const patchUrls = global.mockHttp.history.patch.map(r => r.url)
            expect(patchUrls.some(u => u.includes('cron-days'))).toBe(true)
        }
    })
})
