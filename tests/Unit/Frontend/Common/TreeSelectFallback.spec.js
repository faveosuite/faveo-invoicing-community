import { mount } from '@vue/test-utils'
import { Treeselect } from 'vue3-treeselect'

// Documents the exact vue3-treeselect internals TreeSelect.vue's
// preserveSelected()/ensureSelectedResolved() work around: a selected id
// that isn't in the currently loaded `options` renders as "<id> (unknown)"
// — but a *found* node with an empty label renders blank instead, which is
// why ensureSelectedResolved() seeds a blank placeholder synchronously
// rather than leaving the id unresolved while its real label loads.
describe('vue3-treeselect (unknown) fallback', () => {
    it('falls back to "<id> (unknown)" when the selected id is missing from options', () => {
        const wrapper = mount(Treeselect, {
            props: { modelValue: 99, options: [{ id: 1, label: 'Product A' }], flat: true, disableBranchNodes: true },
        })
        expect(wrapper.find('.vue-treeselect__single-value').text()).toBe('99 (unknown)')
    })

    it('resolves the real label once the selected id is present in options', () => {
        const wrapper = mount(Treeselect, {
            props: {
                modelValue: 99,
                options: [{ id: 1, label: 'Product A' }, { id: 99, label: 'Product Z' }],
                flat: true,
                disableBranchNodes: true,
            },
        })
        expect(wrapper.find('.vue-treeselect__single-value').text()).toBe('Product Z')
    })

    it('renders blank (not "(unknown)") for a found node with an empty label', () => {
        const wrapper = mount(Treeselect, {
            props: { modelValue: 226, options: [{ id: 226, label: '' }], flat: true, disableBranchNodes: true },
        })
        expect(wrapper.find('.vue-treeselect__single-value').text()).toBe('')
        expect(wrapper.html()).not.toContain('unknown')
    })
})
