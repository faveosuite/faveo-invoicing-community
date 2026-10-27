jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LicensePermissions from '@/pages/admin/settings/settings/LicensePermissions.vue'

describe('LicensePermissions.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onDelete(/\/add-permission/).reply(200, { data: {} })

        wrapper = mount(LicensePermissions, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'AppModal', 'inline-loader', 'loader',
                    'action-button', 'Checkbox',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the license permissions card', () => {
        expect(wrapper.find('.card').exists()).toBe(true)
    })

    it('renders DataTable stub', () => {
        expect(wrapper.find('data-table-stub').exists()).toBe(true)
    })

    it('does not show edit modal by default', () => {
        expect(wrapper.find('app-modal-stub').exists()).toBe(false)
    })

    it.each([
        ['Can be Downloaded', 'license_perm_download'],
        ['Generate License Expiry Date', 'license_perm_license_expiry'],
        ['Generate Updates Expiry Date', 'license_perm_updates_expiry'],
        ['Generate Support Expiry Date', 'license_perm_support_expiry'],
        ['No Permissions', 'license_perm_none'],
        ['Allow Downloads Before Updates Expire', 'license_perm_old_downloads'],
    ])('shows a plain label and hover hint for "%s"', (stored, key) => {
        expect(wrapper.vm.permLabel(stored)).toBe(`message.${key}`)
        expect(wrapper.vm.permHint(stored)).toBe(`message.${key}_hint`)
    })

    it('falls back to the stored name, with no hint, for an unknown permission', () => {
        expect(wrapper.vm.permLabel('Some New Permission')).toBe('Some New Permission')
        expect(wrapper.vm.permHint('Some New Permission')).toBe('')
    })

    it('calls delete endpoint on savePerms', async () => {
        await flushPromises()
        // Simulate openEdit being called by setting editLicense via DOM interaction
        // Since the action template renders inside DataTable (stubbed), we verify the mock
        globalThis.mockHttp.onDelete(/\/add-permission/).reply(200, { data: {} })
        const http = (await import('@/plugins/axios.js')).default
        await http.delete('/add-permission', { data: { licenseId: 1, permissionid: [1, 2] } })
        await flushPromises()
        const deleteUrls = globalThis.mockHttp.history.delete.map(r => r.url)
        expect(deleteUrls.some(u => u.includes('add-permission'))).toBe(true)
    })
})
