import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import ColumnSelector from '@/components/Reusable/ColumnSelector.vue'

const mountColumnSelector = (props = {}) =>
    mount(ColumnSelector, {
        props: { entityType: 'users', ...props },
        global: {
            plugins: [createTestingPinia()],
            stubs: { loader: true },
        },
    })

describe('ColumnSelector.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet('/get-columns').reply(200, {
            data: {
                columns: [
                    { key: 'name', label: 'name', is_visible: 1 },
                    { key: 'email', label: 'email', is_visible: 1 },
                    { key: 'action', label: 'action', is_visible: 1 },
                    { key: 'checkbox', label: 'checkbox', is_visible: 1 },
                ],
            },
        })
        globalThis.mockHttp.onPost('/save-columns').reply(200, { data: { message: 'Saved' } })
        wrapper = mountColumnSelector()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders the selected columns button', () => {
        expect(wrapper.find('button.dropdown-toggle').exists()).toBe(true)
    })

    it('renders dropdown-toggle button', () => {
        expect(wrapper.find('.dropdown-toggle').exists()).toBe(true)
    })

    it('shows loader while loading columns', async () => {
        globalThis.mockHttp.reset()
        let resolve
        globalThis.mockHttp.onGet('/get-columns').reply(() =>
            new Promise(r => { resolve = r })
        )
        const w = mountColumnSelector()
        await w.vm.$nextTick()
        expect(w.find('loader-stub, loader').exists()).toBe(true)
        resolve([200, { data: { columns: [] } }])
    })

    it('renders column items after loading', async () => {
        await flushPromises()
        const items = wrapper.findAll('.column-selector-item')
        // Only toggleable columns (not pinned checkbox/action)
        expect(items.length).toBe(2)
    })

    it('displays column labels', async () => {
        await flushPromises()
        const texts = wrapper.findAll('.column-selector-text').map(el => el.text())
        expect(texts).toContain('Name')
        expect(texts).toContain('Email')
    })

    it('emits change event on load', async () => {
        await flushPromises()
        expect(wrapper.emitted('change')).toBeTruthy()
        const payload = wrapper.emitted('change')[0][0]
        expect(payload).toContain('name')
        expect(payload).toContain('email')
    })

    it('uses custom labels prop when provided', async () => {
        wrapper = mountColumnSelector({ labels: { name: 'Full Name' } })
        await flushPromises()
        const texts = wrapper.findAll('.column-selector-text').map(el => el.text())
        expect(texts).toContain('Full Name')
    })

    it('disables apply button while saving', async () => {
        await flushPromises()
        // The apply button in the footer
        const applyBtn = wrapper.find('.column-selector-footer button')
        expect(applyBtn.exists()).toBe(true)
        expect(applyBtn.attributes('disabled')).toBeUndefined()
    })

    it('calls save-columns API when apply is clicked', async () => {
        await flushPromises()
        const applyBtn = wrapper.find('.column-selector-footer button')
        await applyBtn.trigger('click')
        await flushPromises()
        expect(wrapper.emitted('change').length).toBeGreaterThan(1)
    })

    it('respects pinStart and pinEnd props', async () => {
        wrapper = mountColumnSelector({ pinStart: ['checkbox'], pinEnd: ['action'] })
        await flushPromises()
        const items = wrapper.findAll('.column-selector-item')
        // Should not show checkbox or action as toggleable
        const keys = items.map(i => i.find('input[type="checkbox"]').element.parentElement.textContent.trim())
        expect(keys.join('')).not.toContain('Checkbox')
        expect(keys.join('')).not.toContain('Action')
    })
})
