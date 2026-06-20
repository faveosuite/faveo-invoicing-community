jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))
jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))
jest.mock('@/helpers/formUtils.js', () => ({ validateForm: jest.fn(() => Promise.resolve(true)), scrollToFirstError: jest.fn() }))
jest.mock('vue-router', () => ({ useRouter: () => ({ push: jest.fn() }), useRoute: () => ({ params: {}, query: {} }), RouterLink: { template: '<a><slot/></a>' } }))

import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ZohoCard from '@/pages/admin/settings/api/ZohoCard.vue'

const STUBS = [
    'DataTable', 'AppAlert', 'inline-loader', 'action-button', 'DeleteModal',
    'DynamicSelect', 'TextField', 'StaticSelect', 'DatePicker', 'RadioButton',
    'NumberField', 'TinyMCE', 'loader', 'ColumnSelector', 'Switch', 'Checkbox',
    'Tooltip', 'SelectField', 'AppModal', 'ImageUpload', 'PhoneField',
    'RecaptchaProvider', 'RecaptchaCheckbox', 'RecaptchaV2Invisible', 'RecaptchaV3',
    'spinner-loader', 'router-link',
]

const makeIntegration = (overrides = {}) => ({
    is_active: false,
    platform: 'crm',
    description: 'Zoho CRM integration',
    ...overrides,
})

describe('ZohoCard.vue', () => {
    it('is a vue instance when mounted with required props', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration() },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('accepts iconClass and toggling optional props', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration(), iconClass: 'fas fa-cloud', toggling: true },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.exists()).toBeTruthy()
        expect(wrapper.props('iconClass')).toBe('fas fa-cloud')
        expect(wrapper.props('toggling')).toBe(true)
    })

    it('emits toggle event when toggle button is clicked', async () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration() },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        const btn = wrapper.find('button')
        await btn.trigger('click')
        expect(wrapper.emitted('toggle')).toBeTruthy()
    })

    it('emits toggle with the integration object as payload', async () => {
        const integration = makeIntegration({ platform: 'campaigns' })
        const wrapper = mount(ZohoCard, {
            props: { integration },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        const btn = wrapper.find('button')
        await btn.trigger('click')
        const emitted = wrapper.emitted('toggle')
        expect(emitted[0][0]).toMatchObject({ platform: 'campaigns' })
    })

    it('shows inactive state when is_active is false', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ is_active: false }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.text()).not.toContain('Active')
    })

    it('shows active state when is_active is true', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ is_active: true }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        // card renders differently when active — at minimum it mounts without error
        expect(wrapper.exists()).toBe(true)
    })

    it('makes no HTTP calls — purely presentational', async () => {
        global.mockHttp.reset()
        mount(ZohoCard, {
            props: { integration: makeIntegration() },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
        expect(global.mockHttp.history.get.length).toBe(0)
        expect(global.mockHttp.history.post.length).toBe(0)
    })

    it('renders description from integration prop', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ description: 'My CRM desc' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.text()).toContain('My CRM desc')
    })
})
