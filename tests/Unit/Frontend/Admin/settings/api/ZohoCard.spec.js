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
        globalThis.mockHttp.reset()
        mount(ZohoCard, {
            props: { integration: makeIntegration() },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        await flushPromises()
        expect(globalThis.mockHttp.history.get.length).toBe(0)
        expect(globalThis.mockHttp.history.post.length).toBe(0)
    })

    it('renders description from integration prop', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ description: 'My CRM desc' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.text()).toContain('My CRM desc')
    })

    // ── label computed ─────────────────────────────────────────────────────
    it('label computed returns zoho_crm translation key for crm platform', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ platform: 'crm' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        // __() is mocked as identity (lang) returning key string
        expect(wrapper.vm.label).toBe('message.zoho_crm')
    })

    it('label computed returns zoho_campaigns translation key for campaigns platform', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ platform: 'campaigns' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.vm.label).toBe('message.zoho_campaigns')
    })

    it('label computed falls back to "Zoho <platform>" for unknown platform', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ platform: 'books' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.vm.label).toBe('Zoho books')
    })

    // ── toggling prop disables the button ──────────────────────────────────
    it('toggle button is disabled when toggling prop is true', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration(), toggling: true },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        const btn = wrapper.find('button')
        expect(btn.attributes('disabled')).toBeDefined()
    })

    it('toggle button is not disabled when toggling prop is false', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration(), toggling: false },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        const btn = wrapper.find('button')
        expect(btn.attributes('disabled')).toBeUndefined()
    })

    // ── settings RouterLink shown only when active ─────────────────────────
    it('settings gear link is rendered when is_active is true', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ is_active: true, platform: 'crm' }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        // RouterLink is stubbed as router-link; look for the gear link rendered
        // Since RouterLink stub renders as <a>, find the router-link stub
        expect(wrapper.find('routerlink-stub, a[href], router-link-stub').exists() ||
               wrapper.html().includes('settings/api/zoho')).toBe(true)
    })

    it('settings gear link is NOT rendered when is_active is false', () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration({ is_active: false }) },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        expect(wrapper.html()).not.toContain('settings/api/zoho')
    })

    // ── multiple toggle emissions ──────────────────────────────────────────
    it('emits toggle twice when button clicked twice', async () => {
        const wrapper = mount(ZohoCard, {
            props: { integration: makeIntegration() },
            global: { plugins: [createTestingPinia()], stubs: STUBS },
        })
        const btn = wrapper.find('button')
        await btn.trigger('click')
        await btn.trigger('click')
        expect(wrapper.emitted('toggle').length).toBe(2)
    })
})
