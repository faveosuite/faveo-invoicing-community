jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import LanguageTableActions from '@/pages/admin/settings/settings/LanguageTableActions.vue'

describe('LanguageTableActions.vue', () => {
    const defaultProps = {
        status: 1,
        isDefault: false,
        toggling: false,
        settingDefault: false,
    }

    let wrapper

    beforeEach(() => {
        wrapper = mount(LanguageTableActions, {
            props: defaultProps,
            global: {
                plugins: [createTestingPinia()],
            },
        })
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders toggle button', () => {
        expect(wrapper.find('button').exists()).toBe(true)
    })

    it('renders set-default button when status is active and not default', () => {
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBe(2)
    })

    it('emits toggle event when toggle button clicked', async () => {
        const buttons = wrapper.findAll('button')
        await buttons[0].trigger('click')
        expect(wrapper.emitted('toggle')).toBeTruthy()
    })

    it('emits set-default event when set-default button clicked', async () => {
        const buttons = wrapper.findAll('button')
        await buttons[1].trigger('click')
        expect(wrapper.emitted('set-default')).toBeTruthy()
    })

    it('disables toggle button when toggling prop is true', async () => {
        await wrapper.setProps({ toggling: true })
        const toggleBtn = wrapper.find('button')
        expect(toggleBtn.attributes('disabled')).toBeDefined()
    })

    it('disables toggle button when isDefault is true', async () => {
        await wrapper.setProps({ isDefault: true })
        const toggleBtn = wrapper.find('button')
        expect(toggleBtn.attributes('disabled')).toBeDefined()
    })

    it('does not render set-default button when isDefault is true', async () => {
        await wrapper.setProps({ isDefault: true })
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBe(1)
    })

    it('does not render set-default button when status is 0', async () => {
        await wrapper.setProps({ status: 0 })
        const buttons = wrapper.findAll('button')
        expect(buttons.length).toBe(1)
    })

    it('shows toggle-off icon when status is 0', async () => {
        await wrapper.setProps({ status: 0 })
        expect(wrapper.find('.fa-toggle-off').exists()).toBe(true)
    })

    it('shows toggle-on icon when status is 1', () => {
        expect(wrapper.find('.fa-toggle-on').exists()).toBe(true)
    })
})
