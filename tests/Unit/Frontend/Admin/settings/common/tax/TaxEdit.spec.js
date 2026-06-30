jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: { id: '3' }, query: {} }) }))
jest.mock('@/validations/admin/taxValidations', () => ({ buildTaxEditSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import TaxEdit from '@/pages/admin/settings/common/tax/TaxEdit.vue'

const SelectFieldStub = {
    name: 'SelectField',
    props: { name: String, onChange: Function, value: [Object, String, Number], elements: Array, clearable: Boolean, searchable: Boolean },
    template: '<div class="sf-stub" :data-name="name"></div>',
}

describe('TaxEdit.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/tax\/edit\/3/).reply(200, {
            data: {
                tax: {
                    name: 'GST', rate: '10', tax_class: 'standard',
                    country: 'IN', state: '', priority: 1, compound: 0, active: 1,
                },
                postcode: '',
                city: '',
            },
        })
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { IN: 'India', US: 'United States' },
                classes: [{ slug: 'standard', name: 'Standard' }],
            },
        })
        globalThis.mockHttp.onPut(/\/tax\/3/).reply(200, { message: 'Updated' })

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
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThanOrEqual(2)
        const urls = globalThis.mockHttp.history.get.map(r => r.url)
        expect(urls.some(u => u.includes('/tax/edit/3'))).toBe(true)
        expect(urls.some(u => u.includes('/tax-options'))).toBe(true)
    })

    it('handles 500 error on fetch', async () => {
        globalThis.mockHttp.onGet(/\/tax\/edit\/3/).reply(500)
        globalThis.mockHttp.onGet(/\/tax-options/).reply(500)
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

        expect(globalThis.mockHttp.history.put.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.put[0].url).toMatch(/\/tax\/3/)
    })

    it('fetches states when country is selected on edit', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'NY', state_subdivision_name: 'New York' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('/get-state/US'))).toBe(true)
    })
})

describe('TaxEdit.vue — branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax\/edit\/3/).reply(200, {
            data: {
                tax: { name: 'GST', rate: 10, tax_class: '', country: 'IN', state: 'MH', priority: 1, compound: 1, active: 1 },
                postcode: '', city: '',
            },
        })
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: '', name: 'Standard' }],
            },
        })
        globalThis.mockHttp.onGet(/\/get-state\/IN/).reply(200, {
            data: { states: [{ iso2: 'MH', state_subdivision_name: 'Maharashtra' }] },
        })
        globalThis.mockHttp.onPut(/\/tax\/3/).reply(200, { message: 'Updated' })

        wrapper = mount(TaxEdit, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'SelectField', 'NumberField', 'RadioButton', 'action-button', 'loader', 'router-link'],
            },
        })
        await flushPromises()
    })

    it('populates form from loaded tax data', () => {
        expect(wrapper.vm.form.name).toBe('GST')
        expect(wrapper.vm.form.rate).toBe(10)
        expect(wrapper.vm.form.compound).toBe(1)
    })

    it('onCountrySelect clears state and fetches states', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('US')
        expect(wrapper.vm.states).toEqual([{ id: 'CA', name: 'California' }])
    })

    it('onCountrySelect sets empty country when val is null', async () => {
        await wrapper.vm.onCountrySelect(null)
        expect(wrapper.vm.form.country).toBe('')
        expect(wrapper.vm.states).toEqual([])
    })

    it('loadStates returns early when country is empty', async () => {
        wrapper.vm.form.country = ''
        await wrapper.vm.loadStates()
        expect(globalThis.mockHttp.history.get.filter(r => r.url.includes('/get-state/')).length).toBeGreaterThan(0) // from beforeEach mount
        const beforeLen = globalThis.mockHttp.history.get.length
        await wrapper.vm.loadStates()
        expect(globalThis.mockHttp.history.get.length).toBe(beforeLen) // no new request
    })

    it('submit() puts to /tax/id and calls successHandler', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => r.url.includes('/tax/3'))).toBe(true)
        expect(successHandler).toHaveBeenCalled()
    })

    it('submit() calls errorHandler on failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPut(/\/tax\/3/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submit() skips API when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        const before = globalThis.mockHttp.history.put.length
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.put.length).toBe(before)
    })

    it('saving resets to false after submit', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})

describe('TaxEdit.vue — SelectField onChange branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax\/edit\/3/).reply(200, {
            data: {
                tax: { name: 'GST', rate: 10, tax_class: '', country: 'IN', state: '', priority: 1, compound: 0, active: 1 },
                postcode: '', city: '',
            },
        })
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: { countries: { US: 'United States', IN: 'India' }, classes: [{ slug: 'standard', name: 'Standard' }] },
        })
        globalThis.mockHttp.onGet(/\/get-state\/IN/).reply(200, { data: { states: [] } })
        globalThis.mockHttp.onPut(/\/tax\/3/).reply(200, { message: 'Updated' })

        wrapper = mount(TaxEdit, {
            global: {
                plugins: [createTestingPinia()],
                components: { SelectField: SelectFieldStub },
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader'],
            },
        })
        await flushPromises()
    })

    function getSF(name) {
        return wrapper.findAllComponents(SelectFieldStub).find(c => c.props('name') === name)
    }

    it('tax_class onChange sets form.tax_class from val.id', () => {
        getSF('tax_class')?.props('onChange')({ id: 'standard' })
        expect(wrapper.vm.form.tax_class).toBe('standard')
    })

    it('tax_class onChange sets empty string when val is null', () => {
        getSF('tax_class')?.props('onChange')(null)
        expect(wrapper.vm.form.tax_class).toBe('')
    })

    it('state onChange sets form.state from val.id', () => {
        getSF('state')?.props('onChange')({ id: 'MH' })
        expect(wrapper.vm.form.state).toBe('MH')
    })

    it('state onChange sets empty string when val is null', () => {
        getSF('state')?.props('onChange')(null)
        expect(wrapper.vm.form.state).toBe('')
    })

    it('compound onChange sets form.compound from val.id', () => {
        getSF('compound')?.props('onChange')({ id: 1 })
        expect(wrapper.vm.form.compound).toBe(1)
    })

    it('compound onChange sets 0 when val is null', () => {
        getSF('compound')?.props('onChange')(null)
        expect(wrapper.vm.form.compound).toBe(0)
    })

    it('active onChange sets form.active from val.id', () => {
        getSF('active')?.props('onChange')({ id: 0 })
        expect(wrapper.vm.form.active).toBe(0)
    })

    it('active onChange sets 1 when val is null', () => {
        getSF('active')?.props('onChange')(null)
        expect(wrapper.vm.form.active).toBe(1)
    })

    it('country onChange calls onCountrySelect with valid country', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        getSF('country')?.props('onChange')({ id: 'US' })
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('US')
    })

    it('country onChange with null clears country', async () => {
        getSF('country')?.props('onChange')(null)
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('')
    })
})
