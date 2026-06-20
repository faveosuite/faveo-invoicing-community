jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '3' }, query: {} }) }))
jest.mock('@/validations/admin/taxValidations', () => ({ buildTaxEditSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import TaxEdit from '@/pages/admin/settings/common/tax/TaxEdit.vue'

describe('TaxEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onGet(/\/tax\/edit\/3/).reply(200, {
            data: {
                tax: {
                    name: 'GST', rate: '10', tax_class: 'standard',
                    country: 'IN', state: '', priority: 1, compound: 0, active: 1,
                },
                postcode: '',
                city: '',
            },
        })
        global.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { IN: 'India', US: 'United States' },
                classes: [{ slug: 'standard', name: 'Standard' }],
            },
        })
        global.mockHttp.onPut(/\/tax\/3/).reply(200, { message: 'Updated' })

        wrapper = mount(TaxEdit, {
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

    it('fetches tax edit data and options on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBeGreaterThanOrEqual(2)
        const urls = global.mockHttp.history.get.map(r => r.url)
        expect(urls.some(u => u.includes('/tax/edit/3'))).toBe(true)
        expect(urls.some(u => u.includes('/tax-options'))).toBe(true)
    })

    it('handles 500 error on fetch', async () => {
        global.mockHttp.onGet(/\/tax\/edit\/3/).reply(500)
        global.mockHttp.onGet(/\/tax-options/).reply(500)
        const w = mount(TaxEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'TextField', 'SelectField', 'action-button', 'loader', 'inline-loader'],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
        w.unmount()
    })

    it('submits form via PUT to /tax/:id', async () => {
        await flushPromises()
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(true)

        await wrapper.vm.submit()
        await flushPromises()

        expect(global.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(global.mockHttp.history.put[0].url).toMatch(/\/tax\/3/)
    })

    it('fetches states when country is selected on edit', async () => {
        await flushPromises()
        global.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'NY', state_subdivision_name: 'New York' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => r.url.includes('/get-state/US'))).toBe(true)
    })
})
