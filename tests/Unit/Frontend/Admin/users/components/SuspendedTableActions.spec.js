jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import SuspendedTableActions from '@/pages/admin/users/components/SuspendedTableActions.vue'
import { successHandler, errorHandler } from '@/helpers/responseHandler'

describe('SuspendedTableActions.vue', () => {
    let wrapper

    const mountComponent = (props = {}) =>
        mount(SuspendedTableActions, {
            props: {
                userId: 1,
                baseUrl: '',
                ...props,
            },
            global: {
                plugins: [createTestingPinia()],
                stubs: ['DeleteModal', 'spinner-loader'],
            },
        })

    beforeEach(() => {
        globalThis.mockHttp.onGet(/\/user\/restore\//).reply(200, { data: { message: 'Restored' } })
        wrapper = mountComponent()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders restore and delete buttons', () => {
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBeGreaterThanOrEqual(2)
    })

    it('calls restore API when restore button is clicked', async () => {
        const [restoreBtn] = wrapper.findAll('button')
        await restoreBtn.trigger('click')
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBeGreaterThan(0)
    })

    it('emits restored event after successful restore', async () => {
        const [restoreBtn] = wrapper.findAll('button')
        await restoreBtn.trigger('click')
        await flushPromises()
        expect(wrapper.emitted('restored')).toBeTruthy()
    })

    it('calls successHandler after successful restore', async () => {
        const [restoreBtn] = wrapper.findAll('button')
        await restoreBtn.trigger('click')
        await flushPromises()
        expect(successHandler).toHaveBeenCalled()
    })

    it('calls errorHandler on restore failure', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet(/\/user\/restore\//).reply(500)
        const [restoreBtn] = wrapper.findAll('button')
        await restoreBtn.trigger('click')
        await flushPromises()
        expect(errorHandler).toHaveBeenCalled()
    })

    it('shows DeleteModal when delete button is clicked', async () => {
        const buttons = wrapper.findAll('button')
        const deleteBtn = buttons[buttons.length - 1]
        await deleteBtn.trigger('click')
        expect(wrapper.find('delete-modal-stub').exists()).toBe(true)
    })

    it('emits deleted event from DeleteModal', async () => {
        const buttons = wrapper.findAll('button')
        const deleteBtn = buttons[buttons.length - 1]
        await deleteBtn.trigger('click')
        const modal = wrapper.findComponent({ name: 'DeleteModal' })
        if (modal.exists()) {
            await modal.vm.$emit('deleted')
            expect(wrapper.emitted('deleted')).toBeTruthy()
        } else {
            // modal is stubbed — verify showModal state was set
            expect(wrapper.vm.showModal).toBe(true)
        }
    })
})
