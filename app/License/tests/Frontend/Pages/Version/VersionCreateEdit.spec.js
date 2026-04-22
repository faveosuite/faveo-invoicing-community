import { mount } from '@vue/test-utils';
import VersionCreateEdit from '../../../../../Resources/js/Pages/Version/VersionCreateEdit.vue';
import axios from 'axios';
import { useStore } from 'vuex';

jest.mock('axios');
jest.mock('vuex');

describe('VersionCreateEdit.vue', () => {
    let globalConfig;
    let mockStore;

    beforeEach(() => {
        mockStore = {
            getters: {
                getApiKey: 'test-api-key'
            },
            dispatch: jest.fn()
        };
        useStore.mockReturnValue(mockStore);

        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    version_number: '1.0.0',
                    product: [{ product_id: 1, product_title: 'Test Product' }],
                    version_status: 1
                }
            }
        });

        globalConfig = {
            stubs: {
                'custom-loader': true,
                'alert': true,
                'dynamic-select': true,
                'text-field': true,
                'radio-button': true,
                'number-field': true,
                'date-time-picker': true
            },
            mocks: {
                lang: (msg) => msg,
                trans: (msg) => msg,
                basePath: () => '/admin',
            }
        };

        // Mock window.location
        delete window.location;
        window.location = { pathname: '/admin/versions/1/edit' };
    });

    it('renders create mode correctly', async () => {
        window.location.pathname = '/admin/versions/add';
        const wrapper = mount(VersionCreateEdit, {
            global: globalConfig,
        });

        expect(wrapper.vm.title).toBe('create_new_version');
        expect(wrapper.find('h3').text()).toBe('create_new_version');
    });

    it('renders edit mode correctly and fetches data', async () => {
        window.location.pathname = '/admin/versions/1/edit';
        const wrapper = mount(VersionCreateEdit, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.title).toBe('edit_version');
        expect(axios.get).toHaveBeenCalledWith('/api/admin/versionView/1');
        expect(wrapper.vm.version_number).toBe('1.0.0');
    });

    it('updates data on change', async () => {
        const wrapper = mount(VersionCreateEdit, {
            global: globalConfig,
        });

        wrapper.vm.onChange('2.0.0', 'version_number');
        expect(wrapper.vm.version_number).toBe('2.0.0');

        wrapper.vm.onChange({ product_id: 2, product_title: 'New Product' }, 'product');
        expect(wrapper.vm.product_id).toBe(2);
        expect(wrapper.vm.product_title).toBe('New Product');
    });
});
