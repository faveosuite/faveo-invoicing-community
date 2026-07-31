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
                    { id: 1, name: 'Product A (plain)' },
                    { id: 2, name: 'Product B (obfuscated)' },
                ],
            },
            {
                name: 'Group B',
                children: [
                    { id: 3, name: 'Product C (source)' },
                ],
            },
        ],
    },
}

const STUBS = [
    'AppAlert', 'action-button', 'AppButton', 'SelectField', 'Switch',
    'TinyMCE', 'TextArea', 'loader', 'inline-loader', 'spinner-loader',
]

describe('ProductBuildApply.vue', () => {
    let wrapper

    beforeEach(async () => {
        jest.useFakeTimers()

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
        jest.useRealTimers()
    })

    // A selected product with a version + a real uploaded file — the exact
    // minimum submit() will accept, used by every "reaches the API" test below.
    async function makeSubmitValid() {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        wrapper.vm.onFile({ target: { files: [new File(['content'], 'source.zip', { type: 'application/zip' })] } })
        await flushPromises()
    }

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches grouped products on mount, clears loadingProducts, and defaults to the first group', () => {
        expect(wrapper.vm.loadingProducts).toBe(false)
        expect(wrapper.vm.rawGroups.map(g => g.groupName)).toEqual(['Group A', 'Group B'])
        expect(wrapper.vm.activeGroup).toBe('Group A')
    })

    it('selectedProductIds starts empty', () => {
        expect(wrapper.vm.selectedProductIds).toEqual([])
    })

    // ── Browsing: one group / selected-only / search ──────────────────────
    it('visibleProducts defaults to only the active group', () => {
        expect(wrapper.vm.viewMode).toBe('group')
        expect(wrapper.vm.visibleProducts.map(p => p.id)).toEqual([1, 2])
    })

    it('selectGroup switches the active group and clears search/selected-only', () => {
        wrapper.vm.productSearch = 'x'
        wrapper.vm.onlySelected = true
        wrapper.vm.selectGroup('Group B')
        expect(wrapper.vm.activeGroup).toBe('Group B')
        expect(wrapper.vm.productSearch).toBe('')
        expect(wrapper.vm.onlySelected).toBe(false)
        expect(wrapper.vm.visibleProducts.map(p => p.id)).toEqual([3])
    })

    it('search matches by product name across every group, not just the active one', () => {
        wrapper.vm.productSearch = 'product'
        expect(wrapper.vm.viewMode).toBe('search')
        expect(wrapper.vm.visibleProducts.map(p => p.id).sort()).toEqual([1, 2, 3])
    })

    it('search narrows to a single match', () => {
        wrapper.vm.productSearch = 'plain'
        expect(wrapper.vm.visibleProducts).toHaveLength(1)
        expect(wrapper.vm.visibleProducts[0].id).toBe(1)
    })

    it('search with no matches returns an empty list', () => {
        wrapper.vm.productSearch = 'nonexistent'
        expect(wrapper.vm.visibleProducts).toHaveLength(0)
    })

    it('toggleOnlySelected switches to the selected-only tray', () => {
        wrapper.vm.toggleProduct(3)
        wrapper.vm.toggleOnlySelected()
        expect(wrapper.vm.viewMode).toBe('selected')
        expect(wrapper.vm.visibleProducts.map(p => p.id)).toEqual([3])
    })

    // ── Selection ──────────────────────────────────────────────────────────
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

    it('allVisibleSelected is false until every product in the active group is selected', () => {
        wrapper.vm.toggleProduct(1)
        expect(wrapper.vm.allVisibleSelected).toBe(false)
        wrapper.vm.toggleProduct(2)
        expect(wrapper.vm.allVisibleSelected).toBe(true)
    })

    it('toggleAllVisible selects every product in the active group when not fully selected', () => {
        wrapper.vm.toggleAllVisible()
        expect(wrapper.vm.selectedProductIds).toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAllVisible deselects every product in the active group when fully selected', () => {
        wrapper.vm.toggleAllVisible()
        wrapper.vm.toggleAllVisible()
        expect(wrapper.vm.selectedProductIds).not.toEqual(expect.arrayContaining([1, 2]))
    })

    it('toggleAllVisible does not disturb selections from a different group', () => {
        wrapper.vm.toggleProduct(3)
        wrapper.vm.toggleAllVisible()
        expect(wrapper.vm.selectedProductIds).toContain(3)
    })

    // ── Version cascading ───────────────────────────────────────────────────
    it('onMainVersionChange stamps every product across every group', () => {
        wrapper.vm.onMainVersionChange('9.9.9')
        expect(wrapper.vm.productVersions[1]).toBe('9.9.9')
        expect(wrapper.vm.productVersions[2]).toBe('9.9.9')
        expect(wrapper.vm.productVersions[3]).toBe('9.9.9')
    })

    it('onBulkVersionInput stamps only the products currently visible', () => {
        wrapper.vm.onBulkVersionInput('2.0.0')
        expect(wrapper.vm.productVersions[1]).toBe('2.0.0')
        expect(wrapper.vm.productVersions[2]).toBe('2.0.0')
        expect(wrapper.vm.productVersions[3]).toBeUndefined()
    })

    it('a bulk version set for one group does not leak into another group', () => {
        wrapper.vm.onMainVersionChange('1.0.0')
        wrapper.vm.selectGroup('Group B')
        wrapper.vm.onBulkVersionInput('3.0.0')
        expect(wrapper.vm.productVersions[1]).toBe('1.0.0')
        expect(wrapper.vm.productVersions[3]).toBe('3.0.0')
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

    it('submit sets fileError when no file has been uploaded for a selected product', async () => {
        wrapper.vm.toggleProduct(1)
        wrapper.vm.productVersions[1] = '1.0.0'
        wrapper.vm.form.description = 'A description'
        await wrapper.vm.submit()
        expect(wrapper.vm.fileError).toBeTruthy()
        expect(globalThis.mockHttp.history.put.length).toBe(0)
    })

    // ── submit() success ─────────────────────────────────────────────────
    it('submits and navigates to /products on success', async () => {
        await makeSubmitValid()

        await wrapper.vm.submit()
        await flushPromises()

        expect(globalThis.mockHttp.history.put.length).toBe(1)
        const body = JSON.parse(globalThis.mockHttp.history.put[0].data)
        expect(body.filename).toBe('uploaded-file.zip')
        expect(body.products).toEqual([{ id: 1, version: '1.0.0' }])
        expect(successHandler).toHaveBeenCalled()
        // router.push is called inside a setTimeout, after the alert has had
        // a chance to show on this page — advance timers to reach it.
        jest.runAllTimers()
        expect(mockPush).toHaveBeenCalledWith('/products')
    })

    it('calls applyServerValidation with the known form fields on API failure', async () => {
        globalThis.mockHttp.onPut(/\/product\/upload-build\/apply/).reply(422, {
            errors: { description: ['Description is required.'] },
        })
        await makeSubmitValid()

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
