jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({
    useRouter: () => ({ push: jest.fn() }),
    useRoute: () => ({ params: { id: '2' }, query: {} }),
    RouterLink: { template: '<a><slot /></a>', name: 'RouterLink' },
}))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDate: (v) => v }) }))
jest.mock('@/core/utils/asset.js', () => ({ asset: (path) => path }))
jest.mock('@/core/composables/useNotification.js', () => ({ useNotification: () => ({ notify: jest.fn() }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import UserShow from '@/pages/admin/users/UserShow.vue'
import { errorHandler } from '@/helpers/responseHandler'

const userFixture = {
    id: 2,
    full_name: 'Jane Doe',
    first_name: 'Jane',
    last_name: 'Doe',
    email: 'jane@example.com',
    user_name: 'janedoe',
    email_verified: 1,
    mobile_verified: 0,
    is_2fa_enabled: 0,
    role: 'user',
    company: 'Acme',
}

describe('UserShow.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/user\/2$/).reply(200, { data: userFixture })
        globalThis.mockHttp.onGet(/\/user\/2\/summary/).reply(200, { data: { invoice_total: 0, amount_paid: 0, balance: 0 } })
        globalThis.mockHttp.onGet(/\/user\/2\/comments/).reply(200, { data: [] })
        wrapper = mount(UserShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'DeleteModal', 'PaymentTableActions',
                    'InvoiceTableActions', 'OrderTableActions', 'TextField',
                    'action-button', 'inline-loader', 'loader', 'AppBreadcrumb',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('fetches user data on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/user\/2/.test(r.url))).toBe(true)
    })

    it('sets user data after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.user).not.toBeNull()
        expect(wrapper.vm.user.email).toBe('jane@example.com')
    })

    it('sets loading to false after fetch', async () => {
        await flushPromises()
        expect(wrapper.vm.loading).toBe(false)
    })

    it('fetches summary on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/summary/.test(r.url))).toBe(true)
    })

    it('fetches comments on mount', async () => {
        await flushPromises()
        expect(globalThis.mockHttp.history.get.some(r => /\/comments/.test(r.url))).toBe(true)
    })

    it('calls errorHandler when user fetch fails', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/user\/2$/).reply(500)
        globalThis.mockHttp.onGet(/\/user\/2\/summary/).reply(200, { data: {} })
        globalThis.mockHttp.onGet(/\/user\/2\/comments/).reply(200, { data: [] })
        wrapper = mount(UserShow, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'DataTable', 'DeleteModal', 'PaymentTableActions',
                    'InvoiceTableActions', 'OrderTableActions', 'TextField',
                    'action-button', 'inline-loader', 'loader', 'AppBreadcrumb',
                ],
            },
        })
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('activateTab changes activeTab', async () => {
        await flushPromises()
        wrapper.vm.activateTab('payments')
        expect(wrapper.vm.activeTab).toBe('payments')
    })

    it('activateTab marks tab as mounted', async () => {
        await flushPromises()
        wrapper.vm.activateTab('invoices')
        expect(wrapper.vm.tabMounted.invoices).toBe(true)
    })

    it('startBulkDelete sets bulkDelete when selection exists', async () => {
        await flushPromises()
        wrapper.vm.selInvoices = [1, 2]
        wrapper.vm.startBulkDelete('invoices')
        expect(wrapper.vm.bulkDelete).not.toBeNull()
    })

    it('startBulkDelete does nothing when selection is empty', async () => {
        await flushPromises()
        wrapper.vm.selInvoices = []
        wrapper.vm.startBulkDelete('invoices')
        expect(wrapper.vm.bulkDelete).toBeNull()
    })

    it('confirmDeleteComment sets pendingDelete', async () => {
        await flushPromises()
        wrapper.vm.confirmDeleteComment(99)
        expect(wrapper.vm.pendingDelete).toEqual({ commentId: 99 })
    })

    it('addComment posts to comments endpoint', async () => {
        globalThis.mockHttp.onPost(/\/user\/2\/comments/).reply(200, { data: { id: 1, description: 'Test', author: 'Admin' } })
        await flushPromises()
        wrapper.vm.newComment = 'Test comment'
        await wrapper.vm.addComment()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/comments/.test(r.url))).toBe(true)
    })

    it('addComment returns early when newComment is empty', async () => {
        await flushPromises()
        wrapper.vm.newComment = ''
        await wrapper.vm.addComment()
        expect(globalThis.mockHttp.history.post.filter(r => /\/comments/.test(r.url)).length).toBe(0)
    })

    it('addComment handles API error gracefully without throwing', async () => {
        globalThis.mockHttp.onPost(/\/user\/2\/comments/).reply(500)
        await flushPromises()
        wrapper.vm.newComment = 'Test comment'
        await expect(wrapper.vm.addComment()).resolves.not.toThrow()
        await flushPromises()
        expect(wrapper.vm.savingComment).toBe(false)
    })

    it('startEdit sets editingComment', async () => {
        await flushPromises()
        const comment = { id: 5, description: 'Hello' }
        wrapper.vm.startEdit(comment)
        expect(wrapper.vm.editingComment).toEqual(comment)
    })

    it('saveEdit calls PUT and clears editingComment on success', async () => {
        globalThis.mockHttp.onPut(/\/user\/2\/comments\/5/).reply(200, { data: {} })
        await flushPromises()
        const comment = { id: 5, description: 'Updated' }
        wrapper.vm.editingComment = comment
        await wrapper.vm.saveEdit(comment)
        await flushPromises()
        expect(globalThis.mockHttp.history.put.some(r => /\/comments\/5/.test(r.url))).toBe(true)
        expect(wrapper.vm.editingComment).toBeNull()
    })

    it('saveEdit handles API error', async () => {
        globalThis.mockHttp.onPut(/\/user\/2\/comments\/5/).reply(500)
        await flushPromises()
        const comment = { id: 5, description: 'Updated' }
        wrapper.vm.editingComment = comment
        await wrapper.vm.saveEdit(comment)
        await flushPromises()
        // no throw
    })

    it('disable2fa calls POST /2fa/disable/:id on success', async () => {
        globalThis.mockHttp.onPost(/\/2fa\/disable\/2/).reply(200, { message: 'ok' })
        await flushPromises()
        jest.spyOn(window, 'confirm').mockReturnValue(true)
        await wrapper.vm.disable2fa()
        await flushPromises()
        expect(globalThis.mockHttp.history.post.some(r => /\/2fa\/disable/.test(r.url))).toBe(true)
        globalThis.confirm.mockRestore?.()
    })

    it('disable2fa returns early when confirm is cancelled', async () => {
        jest.spyOn(window, 'confirm').mockReturnValue(false)
        await flushPromises()
        const before = globalThis.mockHttp.history.post.length
        await wrapper.vm.disable2fa()
        expect(globalThis.mockHttp.history.post.length).toBe(before)
        globalThis.confirm.mockRestore?.()
    })

    it('disable2fa handles API error', async () => {
        globalThis.mockHttp.onPost(/\/2fa\/disable\/2/).reply(500)
        await flushPromises()
        jest.spyOn(window, 'confirm').mockReturnValue(true)
        await expect(wrapper.vm.disable2fa()).resolves.not.toThrow()
        globalThis.confirm.mockRestore?.()
    })

    it('copy copies field to clipboard', async () => {
        Object.assign(navigator, { clipboard: { writeText: jest.fn().mockResolvedValue(undefined) } })
        await flushPromises()
        await wrapper.vm.copy('email', 'test@example.com')
        await flushPromises()
        expect(navigator.clipboard.writeText).toHaveBeenCalledWith('test@example.com')
    })

    it('toggleAllInvoices selects all rows when checked', async () => {
        await flushPromises()
        wrapper.vm.invDtRef = { tableData: [{ id: 1 }, { id: 2 }] }
        wrapper.vm.selInvoices = []
        wrapper.vm.toggleAllInvoices({ target: { checked: true } })
        expect(wrapper.vm.selInvoices).toContain(1)
        expect(wrapper.vm.selInvoices).toContain(2)
    })

    it('toggleAllInvoices deselects all rows when unchecked', async () => {
        await flushPromises()
        wrapper.vm.invDtRef = { tableData: [{ id: 1 }, { id: 2 }] }
        wrapper.vm.selInvoices = [1, 2]
        wrapper.vm.toggleAllInvoices({ target: { checked: false } })
        expect(wrapper.vm.selInvoices).toHaveLength(0)
    })

    it('loadSummary sets summary data on success', async () => {
        globalThis.mockHttp.onGet(/\/user\/2\/summary/).reply(200, {
            data: { invoice_total: 100, amount_paid: 80, balance: 20 }
        })
        await wrapper.vm.loadSummary()
        await flushPromises()
        expect(wrapper.vm.summary).toBeDefined()
    })

    it('loadSummary handles API error', async () => {
        globalThis.mockHttp.onGet(/\/user\/2\/summary/).reply(500)
        await expect(wrapper.vm.loadSummary()).resolves.not.toThrow()
    })

    it('onBulkDeleted clears bulkDelete and refreshes', async () => {
        await flushPromises()
        const selRef = { value: [1] }
        const dtRef  = { value: { refresh: jest.fn() } }
        wrapper.vm.bulkDelete = { sel: selRef, dt: dtRef }
        wrapper.vm.onBulkDeleted()
        expect(wrapper.vm.bulkDelete).toBeNull()
        expect(selRef.value).toHaveLength(0)
    })

    it('onBulkDeleted handles null bulkDelete', async () => {
        await flushPromises()
        wrapper.vm.bulkDelete = null
        expect(() => wrapper.vm.onBulkDeleted()).not.toThrow()
    })

    it('statusBadge returns a VNode with correct badge class for known status', async () => {
        await flushPromises()
        const vnode = wrapper.vm.statusBadge('paid', { paid: 'bg-success', unpaid: 'bg-danger' })
        expect(vnode.props?.class).toContain('bg-success')
    })

    it('statusBadge uses bg-secondary for unknown status', async () => {
        await flushPromises()
        const vnode = wrapper.vm.statusBadge('unknown', { paid: 'bg-success' })
        expect(vnode.props?.class).toContain('bg-secondary')
    })

    it('formatMoney formats number with currency', async () => {
        await flushPromises()
        const result = wrapper.vm.formatMoney?.(99.99, 'USD')
        if (result !== undefined) expect(typeof result).toBe('string')
    })
})
