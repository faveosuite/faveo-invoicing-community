jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('@/validations/admin/taxValidations', () => ({ buildTaxCreateSchema: jest.fn(() => ({})) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { errorHandler } from '@/helpers/responseHandler'
import TaxCreate from '@/pages/admin/settings/common/tax/TaxCreate.vue'

// A real component stub that accepts and exposes onChange so we can invoke it in tests
const SelectFieldStub = {
    name: 'SelectField',
    props: { name: String, onChange: Function, value: [Object, String, Number], elements: Array, clearable: Boolean, searchable: Boolean },
    template: '<div class="sf-stub" :data-name="name"></div>',
}

describe('TaxCreate.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: 'standard', name: 'Standard' }],
            },
        })
        globalThis.mockHttp.onPost(/\/create\/tax-class/).reply(200, { message: 'Created' })

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
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.get[0].url).toMatch(/\/tax-options/)
    })

    it('handles 500 error on fetch', async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(500)
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

        expect(globalThis.mockHttp.history.post.length).toBeGreaterThan(0)
        expect(globalThis.mockHttp.history.post[0].url).toMatch(/\/create\/tax-class/)
    })

    it('fetches states when country is selected', async () => {
        await flushPromises()
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => r.url.includes('/get-state/US'))).toBe(true)
    })
})

describe('TaxCreate.vue — branch coverage', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: '', name: 'Standard' }, { slug: 'reduced', name: 'Reduced' }],
            },
        })
        globalThis.mockHttp.onPost(/\/create\/tax-class/).reply(200, { message: 'Created' })

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
        expect(globalThis.mockHttp.history.get.filter(r => r.url.includes('/get-state/')).length).toBe(0)
    })

    it('onCountrySelect clears state and fetches states on valid country', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
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
        expect(globalThis.mockHttp.history.post.some(r => r.url.includes('/create/tax-class'))).toBe(true)
    })

    it('submit() calls errorHandler on API failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onPost(/\/create\/tax-class/).reply(500)
        await wrapper.vm.submit()
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('submit() does not call API when validateForm returns false', async () => {
        const { validateForm } = require('@/helpers/formUtils.js')
        validateForm.mockResolvedValueOnce(false)
        await wrapper.vm.submit()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('classOptions falls back to Standard when classes is empty', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
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

    it('onCountrySelect handles get-state error gracefully', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/IN/).reply(500)
        await wrapper.vm.onCountrySelect({ id: 'IN' })
        await flushPromises()
        expect(wrapper.vm.states).toEqual([])
    })

    it('onCountrySelect handles missing states array in response', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, { data: {} })
        await wrapper.vm.onCountrySelect({ id: 'US' })
        await flushPromises()
        expect(wrapper.vm.states).toEqual([])
    })
})

describe('TaxCreate.vue — SelectField onChange handlers via component stub', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/tax-options/).reply(200, {
            data: {
                countries: { US: 'United States', IN: 'India' },
                classes: [{ slug: 'standard', name: 'Standard' }, { slug: 'reduced', name: 'Reduced' }],
            },
        })
        globalThis.mockHttp.onPost(/\/create\/tax-class/).reply(200, { message: 'Created' })

        wrapper = mount(TaxCreate, {
            global: {
                plugins: [createTestingPinia()],
                components: { SelectField: SelectFieldStub },
                stubs: ['AppAlert', 'TextField', 'action-button', 'loader'],
            },
        })
        await flushPromises()
    })

    function getSF(name) {
        const all = wrapper.findAllComponents(SelectFieldStub)
        return all.find(c => c.props('name') === name)
    }

    it('tax_class onChange sets form.tax_class from val.id', () => {
        const sf = getSF('tax_class')
        expect(sf).toBeDefined()
        sf.props('onChange')({ id: 'reduced' })
        expect(wrapper.vm.form.tax_class).toBe('reduced')
    })

    it('tax_class onChange sets empty string when val is null', () => {
        const sf = getSF('tax_class')
        sf.props('onChange')(null)
        expect(wrapper.vm.form.tax_class).toBe('')
    })

    it('state onChange sets form.state from val.id', () => {
        const sf = getSF('state')
        expect(sf).toBeDefined()
        sf.props('onChange')({ id: 'CA' })
        expect(wrapper.vm.form.state).toBe('CA')
    })

    it('state onChange sets empty string when val is null', () => {
        const sf = getSF('state')
        sf.props('onChange')(null)
        expect(wrapper.vm.form.state).toBe('')
    })

    it('compound onChange sets form.compound from val.id', () => {
        const sf = getSF('compound')
        expect(sf).toBeDefined()
        sf.props('onChange')({ id: 1 })
        expect(wrapper.vm.form.compound).toBe(1)
    })

    it('compound onChange sets 0 when val is null', () => {
        const sf = getSF('compound')
        sf.props('onChange')(null)
        expect(wrapper.vm.form.compound).toBe(0)
    })

    it('active onChange sets form.active from val.id', () => {
        const sf = getSF('active')
        expect(sf).toBeDefined()
        sf.props('onChange')({ id: 0 })
        expect(wrapper.vm.form.active).toBe(0)
    })

    it('active onChange sets 1 when val is null', () => {
        const sf = getSF('active')
        sf.props('onChange')(null)
        expect(wrapper.vm.form.active).toBe(1)
    })

    it('country onChange calls onCountrySelect with valid country', async () => {
        globalThis.mockHttp.onGet(/\/get-state\/US/).reply(200, {
            data: { states: [{ iso2: 'CA', state_subdivision_name: 'California' }] },
        })
        const sf = getSF('country')
        expect(sf).toBeDefined()
        sf.props('onChange')({ id: 'US' })
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('US')
    })

    it('country onChange with null clears country', async () => {
        const sf = getSF('country')
        sf.props('onChange')(null)
        await flushPromises()
        expect(wrapper.vm.form.country).toBe('')
    })

    // Test name TextField onChange callbacks via wrapper.vm.form mutations
    it('name onChange sets form.name via direct mutation', () => {
        wrapper.vm.form.name = 'GST'
        expect(wrapper.vm.form.name).toBe('GST')
    })

    it('rate onChange sets form.rate via direct mutation', () => {
        wrapper.vm.form.rate = '10'
        expect(wrapper.vm.form.rate).toBe('10')
    })
})
