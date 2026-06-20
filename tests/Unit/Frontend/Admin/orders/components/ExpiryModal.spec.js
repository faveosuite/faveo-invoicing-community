jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))
jest.mock('vue-datepicker-next', () => ({ __esModule: true, default: { template: '<div class="vue-datepicker-stub"></div>', name: 'VueDatePicker' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ExpiryModal from '@/pages/admin/orders/components/ExpiryModal.vue'

const defaultProps = {
    id: 'expiry-modal',
    title: 'Update Expiry',
    orderId: 42,
    initialDate: '06/20/2026',
    endpoint: 'update-expiry',
    baseUrl: '',
}

describe('ExpiryModal.vue', () => {
    let wrapper

    beforeEach(() => {
        global.mockHttp.onPost(/\/update-expiry/).reply(200, { message: 'success', update: 'Date updated successfully.' })
        wrapper = mount(ExpiryModal, {
            props: defaultProps,
            global: {
                plugins: [createTestingPinia()],
                stubs: ['action-button', 'VueDatePicker'],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the modal title', () => {
        expect(wrapper.find('.modal-title').text()).toBe('Update Expiry')
    })

    it('initialises selectedDate from initialDate prop', () => {
        expect(wrapper.vm.selectedDate).toBe('06/20/2026')
    })

    it('saving starts as false', () => {
        expect(wrapper.vm.saving).toBe(false)
    })

    it('message starts as null', () => {
        expect(wrapper.vm.message).toBeNull()
    })

    it('does not POST when selectedDate is empty', async () => {
        wrapper.vm.selectedDate = null
        await wrapper.vm.save()
        await flushPromises()
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('POSTs to baseUrl/endpoint with orderid and date on save', async () => {
        await wrapper.vm.save()
        await flushPromises()
        expect(global.mockHttp.history.post.some(r => /\/update-expiry/.test(r.url))).toBe(true)
        const body = JSON.parse(global.mockHttp.history.post[0].data)
        expect(body.orderid).toBe(42)
        expect(body.date).toBe('06/20/2026')
    })

    it('sets a success message after successful POST', async () => {
        await wrapper.vm.save()
        await flushPromises()
        expect(wrapper.vm.message).toMatchObject({ type: 'success', text: 'Date updated successfully.' })
    })

    it('emits "saved" after successful POST (after timeout)', async () => {
        jest.useFakeTimers()
        await wrapper.vm.save()
        await flushPromises()
        jest.runAllTimers()
        expect(wrapper.emitted('saved')).toBeTruthy()
        jest.useRealTimers()
    })

    it('sets an error message on POST failure', async () => {
        global.mockHttp.reset()
        global.mockHttp.onPost(/\/update-expiry/).reply(500, { message: 'Server error' })
        await wrapper.vm.save()
        await flushPromises()
        expect(wrapper.vm.message).toMatchObject({ type: 'error' })
    })

    it('saving resets to false after POST completes', async () => {
        await wrapper.vm.save()
        await flushPromises()
        expect(wrapper.vm.saving).toBe(false)
    })

    it('watch updates selectedDate when initialDate prop changes', async () => {
        await wrapper.setProps({ initialDate: '12/31/2026' })
        expect(wrapper.vm.selectedDate).toBe('12/31/2026')
    })
})
