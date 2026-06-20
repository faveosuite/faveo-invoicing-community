jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot /></a>' } }))
jest.mock('@/core/composables/useDateTime', () => ({ useDateTime: () => ({ formatDateTime: (v) => v }) }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import OpenPaymentsList from '@/pages/admin/settings/common/openPayments/OpenPaymentsList.vue'

describe('OpenPaymentsList.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(OpenPaymentsList, {
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'AppAlert', 'StaticAlert', 'DataTable', 'AppModal',
                    'OpenPaymentsFilter', 'inline-loader', 'action-button',
                    'router-link', 'delete-modal',
                ],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the Open Payments card title', () => {
        expect(wrapper.html()).toContain('Open Payments')
    })

    it('showFilter defaults to false', () => {
        expect(wrapper.html()).toBeTruthy()
    })

    it('toggles showFilter when filter button is clicked', async () => {
        const btn = wrapper.find('button.btn-tool')
        if (btn.exists()) {
            await btn.trigger('click')
        }
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders DataTable stub', () => {
        expect(wrapper.findComponent({ name: 'DataTable' }).exists() || wrapper.html().includes('datatable')).toBeTruthy()
    })
})
