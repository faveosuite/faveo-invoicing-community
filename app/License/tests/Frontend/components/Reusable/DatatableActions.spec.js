import { mount } from '@vue/test-utils';
import DatatableActions from '../../../../../Resources/js/components/Reusable/DatatableActions.vue';
import { createStore } from 'vuex';

describe('DatatableActions.vue', () => {
    let store;
    let propsData;

    beforeEach(() => {
        store = createStore({
            actions: {
                unsetValidationError: jest.fn(),
            },
        });

        propsData = {
            data: {
                edit_url: '/edit/1',
                delete_url: '/delete/1',
                view_url: '/view/1',
                restore_url: '',
                is_default: 0,
                keyVal: 'id',
                idVal: 1,
            },
        };
    });

    const mountComponent = (props = propsData) => {
        return mount(DatatableActions, {
            props,
            global: {
                plugins: [store],
                mocks: {
                    trans: (key) => key,
                },
                directives: {
                    tooltip: {},
                },
                stubs: {
                    'router-link': true,
                    'delete-modal': true,
                },
            },
        });
    };

    it('renders edit, delete and view links when provided', () => {
        const wrapper = mountComponent();
        expect(wrapper.find('router-link-stub[to="/edit/1"]').exists()).toBe(true);
        expect(wrapper.find('button .fa-trash').exists()).toBe(true);
        expect(wrapper.find('router-link-stub[to="/view/1"]').exists()).toBe(true);
    });

    it('does not render edit link when edit_url is missing', () => {
        propsData.data.edit_url = '';
        const wrapper = mountComponent();
        expect(wrapper.find('.fa-edit').exists()).toBe(false);
    });

    it('disables delete button when is_default is true', () => {
        propsData.data.is_default = 1;
        const wrapper = mountComponent();
        const deleteBtn = wrapper.find('button .fa-trash').element.closest('button');
        expect(deleteBtn.disabled).toBe(true);
    });

    it('shows delete modal when delete button is clicked', async () => {
        const wrapper = mountComponent();
        await wrapper.find('button .fa-trash').trigger('click');
        expect(wrapper.vm.showModal).toBe(true);
    });

    it('renders restore button when restore_url is provided', () => {
        propsData.data.restore_url = '/restore/1';
        const wrapper = mountComponent();
        expect(wrapper.find('.fa-sync-alt').exists()).toBe(true);
    });

    it('shows restore modal when restore button is clicked', async () => {
        propsData.data.restore_url = '/restore/1';
        const wrapper = mountComponent();
        await wrapper.find('button .fa-sync-alt').trigger('click');
        expect(wrapper.vm.showRestoreModal).toBe(true);
    });
});
