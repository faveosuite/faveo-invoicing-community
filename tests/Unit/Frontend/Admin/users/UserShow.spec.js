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
        global.mockHttp.onGet(/\/user\/2$/).reply(200, { data: userFixture })
        global.mockHttp.onGet(/\/user\/2\/summary/).reply(200, { data: { invoice_total: 0, amount_paid: 0, balance: 0 } })
        global.mockHttp.onGet(/\/user\/2\/comments/).reply(200, { data: [] })
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
        expect(global.mockHttp.history.get.some(r => /\/user\/2/.test(r.url))).toBe(true)
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
        expect(global.mockHttp.history.get.some(r => /\/summary/.test(r.url))).toBe(true)
    })

    it('fetches comments on mount', async () => {
        await flushPromises()
        expect(global.mockHttp.history.get.some(r => /\/comments/.test(r.url))).toBe(true)
    })

    it('calls errorHandler when user fetch fails', async () => {
        global.mockHttp.reset()
        global.mockHttp.onGet(/\/user\/2$/).reply(500)
        global.mockHttp.onGet(/\/user\/2\/summary/).reply(200, { data: {} })
        global.mockHttp.onGet(/\/user\/2\/comments/).reply(200, { data: [] })
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
        global.mockHttp.onPost(/\/user\/2\/comments/).reply(200, { data: { id: 1, description: 'Test', author: 'Admin' } })
        await flushPromises()
        wrapper.vm.newComment = 'Test comment'
        await wrapper.vm.addComment()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => /\/comments/.test(r.url))).toBe(true)
    })
})
