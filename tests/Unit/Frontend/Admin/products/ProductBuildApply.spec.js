jest.mock('@vueform/toggle', () => ({ __esModule: true, default: { template: '<div />', props: ['modelValue', 'value', 'onLabel', 'offLabel'] } }))
jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({
    successHandler: jest.fn(),
    errorHandler: jest.fn(),
    applyServerValidation: jest.fn(),
}))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
const mockPush = jest.fn()
jest.mock('vue-router', () => ({ useRouter: () => ({ push: mockPush }), useRoute: () => ({ params: {}, query: {} }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { successHandler, applyServerValidation } from '@/helpers/responseHandler'
import ProductBuildApply from '@/pages/admin/products/ProductBuildApply'

const PRODUCTS_RESPONSE = {
    data: {
        products: [
            {
                name: 'Group A',
                children: [
                    { id: 1, name: 'Product A (plain)', build_type: '' },
                    { id: 2, name: 'Product B (obfuscated)', build_type: 'obfuscated' },
                ],
            },
            {
                name: 'Group B',
                children: [
                    { id: 3, name: 'Product C (source)', build_type: 'source' },
                ],
            },
        ],
    },
}

const STUBS = [
    'AppAlert', 'action-button', 'AppButton', 'SelectField', 'Switch',
    'TinyMCE', 'TextArea', 'ToolTip', 'loader', 'inline-loader',
]

describe('ProductBuildApply.vue', () => {
    let wrapper

    beforeEach(async () => {
        globalThis.mockHttp.onGet(/\/dependency\/products/).reply(200, PRODUCTS_RESPONSE)
        globalThis.mockHttp.onPost(/\/chunkupload/).reply(200, { name: 'uploaded-file.zip' })
        globalThis.mockHttp.onPut(/\/product\/upload-build\/apply/).reply(200, { data: { message: 'Applied' } })

        wrapper = mount(ProductBuildApply, {
            global: {
                plugins: [createTestingPinia()],
                stubs: STUBS,
            },
        })
        await flushPromises()
    })

    afterEach(() => {
        globalThis.mockHttp.reset()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches grouped products on mount and clears loadingProducts', () => {
        expect(wrapper.vm.loadingProducts).toBe(false)
        expect(wrapper.vm.groupedProducts.map(g => g.groupName)).toEqual(['Group A', 'Group B'])
    })

    it('selectedProductIds starts empty', () => {
        expect(wrapper.vm.selectedProductIds).toEqual([])
    })

    // ── Search filtering ────────────────────────────────────────────────
    it('groupedProducts returns everything when search is empty', () => {
        expect(wrapper.vm.groupedProducts).toHaveLength(2)
    })

    it('groupedProducts filters products by name within a group', () => {
        wrapper.vm.productSearch = 'plain'
        expect(wrapper.vm.groupedProducts).toHaveLength(1)
        expect(wrapper.vm.groupedProducts[0].groupName).toBe('Group A')
        expect(wrapper.vm.groupedProducts[0].products).toHaveLength(1)
        expect(wrapper.vm.groupedProducts[0].products[0].id).toBe(1)
    })

    it('groupedProducts keeps every product in a group when the group name matches', () => {
        wrapper.vm.productSearch = 'group a'
        expect(wrapper.vm.groupedProducts[0].products).toHaveLength(2)
    })

    it('groupedProducts drops groups with no matches', () => {
        wrapper.vm.productSearch = 'nonexistent'
        expect(wrapper.vm.groupedProducts).toHaveLength(0)
    })

    // ── Selection ────────────────────────────────────────────────────────
    it('toggleProduct adds an id when not already selected', () => {
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.selectedProductIds).toContain(1)
    })

    it('toggleProduct removes an id when already selected', () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.selectedProductIds).not.toContain(1)
    })

    it('toggleProduct clears any existing products field error', () => {
        wrapper.vm.setFieldError('products', 'Select at least one product.')
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.errors.products).toBeUndefined()
    })

    it('isGroupFullySelected is false until every product in the group is selected', () => {
        const group = wrapper.vm.groupedProducts[0]
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.isGroupFullySelected(group)).toBe(false)
        wrapper.vm.toggleProduct(2)
        expect(wrapper.vm.isGroupFullySelected(group)).toBe(true)
    })

    it('toggleGroup selects every product in the group when not fully selected', () => {
        const group = wrapper.vm.groupedProducts[0]
        wrapper.vm.toggleGroup(group)
        expect(wrapper.vm.selectedProductIds).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleGroup deselects every product in the group when fully selected', () => {
        const group = wrapper.vm.groupedProducts[0]
        wrapper.vm.toggleGroup(group)
        wrapper.vm.toggleGroup(group)
        expect(wrapper.vm.selectedProductIds).not.toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleGroup does not disturb selections from other groups', () => {
        wrapper.vm.toggleProduct(3)
        wrapper.vm.toggleGroup(wrapper.vm.groupedProducts[0])
        expect(wrapper.vm.selectedProductIds).toContain(3)
    })

    // ── Version cascading ─────────────────────────────────────────────────
    it('onMainVersionChange stamps every product across every group', () => {
        wrapper.vm.onMainVersionChange('9.9.9')
        expect(wrapper.vm.productVersions[1]).toBe('9.9.9')
        expect(wrapper.vm.productVersions[2]).toBe('9.9.9')
        expect(wrapper.vm.productVersions[3]).toBe('9.9.9')
    })

    it('onGroupVersionChange stamps only that group\'s products', () => {
        const groupA = wrapper.vm.groupedProducts[0]
        wrapper.vm.onGroupVersionChange(groupA, '2.0.0')
        expect(wrapper.vm.productVersions[1]).toBe('2.0.0')
        expect(wrapper.vm.productVersions[2]).toBe('2.0.0')
        expect(wrapper.vm.productVersions[3]).toBeUndefined()
    })

    it('a later group version change does not override an unrelated group', () => {
        wrapper.vm.onMainVersionChange('1.0.0')
        wrapper.vm.onGroupVersionChange(wrapper.vm.groupedProducts[1], '3.0.0')
        expect(wrapper.vm.productVersions[1]).toBe('1.0.0')
        expect(wrapper.vm.productVersions[3]).toBe('3.0.0')
    })

    // ── needsSource / needsObfuscated ──────────────────────────────────────
    it('needsSource/needsObfuscated are both false with nothing selected', () => {
        expect(wrapper.vm.needsSource).toBe(false)
        expect(wrapper.vm.needsObfuscated).toBe(false)
    })

    it('selecting a plain (non-obfuscated) product requires source only', () => {
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.needsSource).toBe(true)
        expect(wrapper.vm.needsObfuscated).toBe(false)
    })

    it('selecting an obfuscated product requires obfuscated only', () => {
        wrapper.vm.toggleProduct(2)
        expect(wrapper.vm.needsObfuscated).toBe(true)
        expect(wrapper.vm.needsSource).toBe(false)
    })

    it('selecting both requires source and obfuscated', () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.toggleProduct(2)
        expect(wrapper.vm.needsSource).toBe(true)
        expect(wrapper.vm.needsObfuscated).toBe(true)
    })

    // ── submit() validation ────────────────────────────────────────────────
    it('submit sets a products error and skips the API call when nothing is selected', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.errors.products).toBeTruthy()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    it('submit sets a products error when a selected product has no version', async () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.form.description = 'A description'
        await wrapper.vm.submit()
        expect(wrapper.vm.errors.products).toBeTruthy()
    })

    it('submit sets a description error when description is blank', async () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = '<p></p>'
        await wrapper.vm.submit()
        expect(wrapper.vm.errors.description).toBeTruthy()
    })

    it('submit sets a dependencies error on invalid JSON', async () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        wrapper.vm.form.dependencies = 'not json'
        await wrapper.vm.submit()
        expect(wrapper.vm.errors.dependencies).toBeTruthy()
    })

    it('submit sets sourceFileError when the required source file is missing', async () => {
        wrapper.vm.toggleProduct(1) // plain product — needs source
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        await wrapper.vm.submit()
        expect(wrapper.vm.sourceFileError).toBeTruthy()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    it('submit sets fileError when the required obfuscated file is missing', async () => {
        wrapper.vm.toggleProduct(2) // obfuscated product — needs the obfuscated slot
        wrapper.vm.productVersions[2] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        await wrapper.vm.submit()
        expect(wrapper.vm.fileError).toBeTruthy()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    // ── submit() success ─────────────────────────────────────────────────
    it('submits and navigates to /products on success', async () => {
        wrapper.vm.toggleProduct(1) // plain product — only needs source
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        wrapper.vm.onSourceFile({ target: { files: [new File(['content'], 'source.zip', { type: 'application/zip' })] } })
        await flushPromises()

        await wrapper.vm.submit()
        await flushPromises()

        expect(globalThis.mockHttp.history.put.length).toBe(1)
        const body = JSON.parse(globalThis.mockHttp.history.put[0].data)
        expect(body.filename_source).toBe('uploaded-file.zip')
        expect(body.products).toEqual([{ id: 1, version: '1.0.0' }])
        expect(successHandler).toHaveBeenCalled()
        expect(mockPush).toHaveBeenCalledWith('/products')
    })

    it('calls applyServerValidation with the known form fields on API failure', async () => {
        globalThis.mockHttp.onPut(/\/product\/upload-build\/apply/).reply(422, {
            errors: { description: ['Description is required.'] },
        })
        wrapper.vm.toggleProduct(1)
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        wrapper.vm.onSourceFile({ target: { files: [new File(['content'], 'source.zip', { type: 'application/zip' })] } })
        await flushPromises()

        await wrapper.vm.submit()
        await flushPromises()

        expect(applyServerValidation).toHaveBeenCalledWith(
            expect.anything(),
            expect.objectContaining({ fields: ['description', 'dependencies', 'products'], component: 'product-build-apply' }),
        )
    })

    it('sets saving false after submit regardless of outcome', async () => {
        await wrapper.vm.submit()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })
})
