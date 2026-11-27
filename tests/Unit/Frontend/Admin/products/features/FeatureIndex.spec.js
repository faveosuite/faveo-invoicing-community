jest.mock('@/helpers/responseHandler', () => ({ successHandler: jest.fn(), errorHandler: jest.fn() }))

import { mount } from '@vue/test-utils'
import FeatureIndex from '@/pages/admin/products/features/FeatureIndex.vue'

async function make() {
    globalThis.mockHttp.onGet('/entitlements').reply(200, { data: { entitlements: [] } })
    globalThis.mockHttp.onPost('/entitlements').reply(200, { data: { message: 'ok' } })
    const wrapper = mount(FeatureIndex, {
        global: { stubs: ['DataTable', 'AppAlert', 'AppModal', 'DeleteModal', 'action-button', 'TextField', 'StaticSelect', 'Checkbox', 'RadioButton'] },
    })
    wrapper.vm.openEditor()
    Object.assign(wrapper.vm.editing, { label: 'Storage', type: 'number', default_value: '' })
    return wrapper
}

describe('FeatureIndex.vue', () => {
    afterEach(() => globalThis.mockHttp.reset())

    it('a number needs a default or Unlimited ticked; unlimited is saved as empty', async () => {
        const wrapper = await make()
        await wrapper.vm.saveEditor()
        expect(wrapper.vm.errors.default_value).toBe('message.entitlement_limit_required')
        expect(globalThis.mockHttp.history.post).toHaveLength(0)

        wrapper.vm.setLimit('', true)
        await wrapper.vm.saveEditor()
        const sent = JSON.parse(globalThis.mockHttp.history.post[0].data).entitlements
        expect(sent[0]).toMatchObject({ code: 'storage', type: 'number', default_value: null })
        expect(sent[0]).not.toHaveProperty('unlimited')
    })

    it('text needs a default', async () => {
        const wrapper = await make()
        Object.assign(wrapper.vm.editing, { type: 'text' })
        await wrapper.vm.saveEditor()
        expect(wrapper.vm.errors.default_value).toBe('message.entitlement_default_required')
        expect(globalThis.mockHttp.history.post).toHaveLength(0)
    })

    it('asks before closing the editor with unsaved changes', async () => {
        const wrapper = await make() // the editor is open with a typed name
        const confirmSpy = jest.spyOn(window, 'confirm').mockReturnValue(false)
        wrapper.vm.closeEditor()
        expect(confirmSpy).toHaveBeenCalledWith('message.unsaved_changes_confirm')
        expect(wrapper.vm.editing).not.toBeNull() // stayed

        wrapper.vm.openEditor()
        wrapper.vm.closeEditor() // nothing changed: closes without asking
        expect(confirmSpy).toHaveBeenCalledTimes(1)
        expect(wrapper.vm.editing).toBeNull()
        confirmSpy.mockRestore()
    })

    it('row buttons name the feature they act on', () => {
        const wrapper = mount(FeatureIndex, { global: { stubs: ['DataTable', 'AppAlert', 'AppModal', 'DeleteModal', 'action-button'] } })
        const buttons = wrapper.vm.tableOptions.templates.action(null, { id: 1, label: 'SLA', is_active: false, in_use: true }).children.filter(Boolean)
        expect(buttons.map(b => b.props['aria-label'])).toEqual(['message.edit SLA', 'message.restore SLA'])
        expect(buttons.every(b => [b.children].flat()[0].props['aria-hidden'] === 'true')).toBe(true)
    })

    it('offers the categories already in use, and a new one typed in another case reuses the existing one', async () => {
        globalThis.mockHttp.onGet('/entitlements').reply(200, { data: { entitlements: [
            { id: 1, category: 'Channels' }, { id: 2, category: 'Plugins' }, { id: 3, category: 'Channels' }, { id: 4, category: null },
        ] } })
        const wrapper = mount(FeatureIndex, { global: { stubs: ['DataTable', 'AppAlert', 'AppModal', 'DeleteModal', 'action-button'] } })
        wrapper.vm.openEditor()
        await flushPromises()
        expect(wrapper.vm.categoryOptions).toEqual([{ name: 'Channels' }, { name: 'Plugins' }])
        expect(wrapper.vm.toCategory('channels ')).toEqual({ name: 'Channels' })
        expect(wrapper.vm.toCategory('Reports')).toEqual({ name: 'Reports' })
    })
})
