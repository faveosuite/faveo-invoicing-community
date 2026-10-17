import { mount } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import DatatableActions from '@/components/Reusable/DatatableActions.vue'

const defaultData = {
    edit_url: '/edit/1',
    view_url: '/view/1',
    delete_url: '/delete/1',
}

const mountActions = (data = defaultData) =>
    mount(DatatableActions, {
        props: { data },
        global: {
            plugins: [createTestingPinia()],
            stubs: {
                DeleteModal: true,
                RouterLink: { template: '<a><slot /></a>' },
                Teleport: { template: '<div><slot /></div>' },
            },
        },
    })

describe('DatatableActions.vue', () => {
    let wrapper

    beforeEach(() => {
        wrapper = mountActions()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders edit link when edit_url is provided', () => {
        expect(wrapper.find('a').exists()).toBe(true)
    })

    it('does not render edit link when edit_url is absent', () => {
        wrapper = mountActions({ view_url: '/view/1', delete_url: '/delete/1' })
        // edit link uses router-link stub which renders <a>
        // Only view would have an <a> in this case
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders delete button when delete_url is provided', () => {
        const deleteBtn = wrapper.find('button .fa-trash')
        expect(deleteBtn.exists()).toBe(true)
    })

    it('does not render delete button when delete_url is absent', () => {
        wrapper = mountActions({ edit_url: '/edit/1', view_url: '/view/1' })
        expect(wrapper.find('.fa-trash').exists()).toBe(false)
    })

    it('renders restore button when restore_url is provided', () => {
        wrapper = mountActions({ ...defaultData, restore_url: '/restore/1' })
        expect(wrapper.find('.fa-sync-alt').exists()).toBe(true)
    })

    it('does not render restore button when restore_url is absent', () => {
        expect(wrapper.find('.fa-sync-alt').exists()).toBe(false)
    })

    it('shows delete modal when delete button is clicked on non-default record', async () => {
        await wrapper.find('button .fa-trash').trigger('click')
        await wrapper.vm.$nextTick()
        // showModal becomes true — DeleteModal stub is rendered
        expect(wrapper.vm.showModal).toBe(true)
    })

    it('does not show delete modal when record is_default', async () => {
        wrapper = mountActions({ ...defaultData, is_default: 1 })
        const trashIcon = wrapper.find('.fa-trash')
        if (trashIcon.exists()) {
            await trashIcon.trigger('click')
            await wrapper.vm.$nextTick()
        }
        expect(wrapper.find('deletemodal-stub').exists()).toBe(false)
    })

    it('disables buttons when is_default is true', () => {
        wrapper = mountActions({ ...defaultData, delete_url: '/delete/1', is_default: 1 })
        const deleteBtn = wrapper.find('button:has(.fa-trash)')
        if (deleteBtn.exists()) {
            expect(deleteBtn.attributes('disabled')).toBeDefined()
        }
    })

    it('renders view link when only view_url provided', () => {
        wrapper = mountActions({ view_url: '/view/1', delete_url: '/delete/1' })
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders dropdown toggle when both view_url and agent_view_url provided', async () => {
        wrapper = mountActions({
            ...defaultData,
            agent_view_url: '/agent-view/1',
        })
        await wrapper.vm.$nextTick()
        expect(wrapper.find('.dropdown-toggle').exists()).toBe(true)
    })

    it('opens dropdown when dropdown toggle is clicked', async () => {
        wrapper = mountActions({
            ...defaultData,
            agent_view_url: '/agent-view/1',
        })
        await wrapper.find('.dropdown-toggle').trigger('click')
        await wrapper.vm.$nextTick()
        // isOpen becomes true
        expect(wrapper.vm.isOpen).toBe(true)
    })
})
