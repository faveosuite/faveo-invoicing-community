jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SocialLoginIndex from '@/pages/admin/settings/settings/socialLogins/SocialLoginIndex.vue'

describe('SocialLoginIndex.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(SocialLoginIndex, {
            global: {
                plugins: [createTestingPinia()],
                stubs: ['AppAlert', 'DataTable', 'router-link'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the DataTable stub', () => {
        expect(wrapper.html().includes('data-table-stub')).toBeTruthy()
    })

    it('has the correct table columns defined', () => {
        expect(wrapper.vm.columns).toEqual(['type', 'status', 'action'])
    })

    it('has sortable columns configured', () => {
        expect(wrapper.vm.tableOptions.sortable).toContain('type')
        expect(wrapper.vm.tableOptions.sortable).toContain('status')
    })
})
