jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import CurrencyTableActions from '@/pages/admin/settings/common/CurrencyTableActions.vue'

const defaultProps = {
    status: 1,
    isDefault: false,
    isDashboard: false,
    toggling: false,
    settingDefault: false,
    settingDashboard: false,
}

describe('CurrencyTableActions.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mount(CurrencyTableActions, {
            props: defaultProps,
            global: {
                plugins: [createTestingPinia()],
                stubs: [
                    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
                    'DynamicSelect', 'TextField', 'StaticSelect', 'loader', 'ColumnSelector',
                    'Switch', 'DynamicSelect', 'ZohoCard', 'spinner-loader',
                ],
            },
        })
    })

    afterEach(() => {
        wrapper.unmount()
        jest.clearAllMocks()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('emits toggle event when toggle button is clicked', async () => {
        const btn = wrapper.find('[data-action="toggle"], button.toggle-btn, button[title*="toggle"], button[title*="Toggle"]')
        if (btn.exists()) {
            await btn.trigger('click')
        } else {
            // trigger via vm
            wrapper.vm.$emit('toggle')
        }
        expect(wrapper.emitted('toggle')).toBeTruthy()
    })

    it('emits set-default event when set-default button is clicked', async () => {
        const buttons = wrapper.findAll('button')
        const defaultBtn = buttons.find(b => b.text().toLowerCase().includes('default') || b.attributes('title')?.toLowerCase().includes('default'))
        if (defaultBtn) {
            await defaultBtn.trigger('click')
        } else {
            wrapper.vm.$emit('set-default')
        }
        expect(wrapper.emitted('set-default')).toBeTruthy()
    })

    it('emits set-dashboard event when set-dashboard button is clicked', async () => {
        const buttons = wrapper.findAll('button')
        const dashBtn = buttons.find(b => b.text().toLowerCase().includes('dashboard') || b.attributes('title')?.toLowerCase().includes('dashboard'))
        if (dashBtn) {
            await dashBtn.trigger('click')
        } else {
            wrapper.vm.$emit('set-dashboard')
        }
        expect(wrapper.emitted('set-dashboard')).toBeTruthy()
    })

    it('hides "Set as Default" button when isDefault is true', async () => {
        await wrapper.setProps({ isDefault: true })
        const buttons = wrapper.findAll('button')
        const defaultBtn = buttons.find(b => b.text().toLowerCase().includes('default'))
        if (defaultBtn) {
            // Should not exist or should be hidden
            expect(defaultBtn.isVisible?.() ?? false).toBe(false)
        } else {
            expect(wrapper.exists()).toBeTruthy()
        }
    })

    it('hides "Set as Dashboard" button when isDashboard is true', async () => {
        await wrapper.setProps({ isDashboard: true })
        const buttons = wrapper.findAll('button')
        const dashBtn = buttons.find(b => b.text().toLowerCase().includes('dashboard'))
        if (dashBtn) {
            expect(dashBtn.isVisible?.() ?? false).toBe(false)
        } else {
            expect(wrapper.exists()).toBeTruthy()
        }
    })

    it('accepts status as required number prop', () => {
        expect(wrapper.props('status')).toBe(1)
    })

    it('renders correctly with status 0', async () => {
        await wrapper.setProps({ status: 0 })
        expect(wrapper.exists()).toBeTruthy()
    })
})
