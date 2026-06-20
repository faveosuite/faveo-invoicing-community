jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/taxValidations', () => ({ buildTaxCreateSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import TaxCreate from '@/pages/admin/settings/common/tax/TaxCreate.vue'

describe('TaxCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: 'standard', name: 'Standard' }],
            },
        })
        global.mockHttp.onPost(/\/create\/tax-class/).reply(200, { message: 'Created' })

        wrapper = mount(TaxCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'TextField', 'SelectField', 'action-button',
                    'loader', 'inline-loader',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches tax options on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.get[0].url).toMatch(/\/tax-options/)
    })

    it('handles 500 error on fetch', async () => {
        global.mockHttp.onGet(/\/tax-options/).reply(500)
        const w = mount(TaxCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'SelectField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via POST to /create/tax-class', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.submit()
        await flushPromises()

        expect(global.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.post[0].url).toMatch(/\/create\/tax-class/)
    })

    it('fetches states when country is selected', async () => {
        await flushPromises()
        global.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => r.url.includes('/get-state/US'))).toBe(true)
    })
})

describe('TaxCreate.vue — branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        global.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: '', name: 'Standard' }, { slug: 'reduced', name: 'Reduced' }],
            },
        })
        global.mockHttp.onPost(/\/create\/tax-class/).reply(200, { message: 'Created' })

        wrapper = mount(TaxCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SelectField', 'NumberField', 'RadioButton', 'action-button', 'loader', 'router-link'],
            },
        })
        await flushPromises()
    })

    it('onCountrySelect returns early when country is empty', async () => {
        await wrapper.vm.onCountrySelect({ id: '' })
        expect(global.mockHttp.history.get.filter(r => r.url.includes('/get-state/')).length).toBe(0)
    })

    it('onCountrySelect clears state and fetches states on valid country', async () => {
        global.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('US')
        expect(wrapper.vm.states).toEqual([{ id: 'CA', name: 'California' }])
    })

    it('onCountrySelect clears form.country when val is null', async () => {
        await wrapper.vm.onCountrySelect(null)
        expect(wrapper.vm.form.country).toBe('')
    })

    it('submit() calls validateForm and posts to create/tax-class on success', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => r.url.includes('/create/tax-class'))).toBe(true)
    })

    it('submit() calls errorHandler on API failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/create\/tax-class/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submit() does not call API when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('classOptions falls back to Standard when classes is empty', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: { countries: {}, classes: [] },
        })
        const w = mount(TaxCreate, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SelectField', 'NumberField', 'RadioButton', 'action-button', 'loader', 'router-link'],
            },
        })
        await flushPromises()
        expect(w.vm.classOptions).toEqual([{ id: '', name: 'Standard' }])
        w.unmount()
    })

    it('saving resets to false after submit completes', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})
