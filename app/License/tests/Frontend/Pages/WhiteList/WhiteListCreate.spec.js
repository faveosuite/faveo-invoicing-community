import { mount } from '@vue/test-utils';
import WhiteListCreate from '../../../../../Resources/js/Pages/WhiteList/WhiteListCreate.vue';
import axios from 'axios';

jest.mock('axios');

describe('WhiteListCreate.vue', () => {
    let globalConfig;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    host_data: {
                        whitelist_host_ip: '127.0.0.1',
                        whitelist_host_comments: 'Localhost'
                    }
                }
            }
        });

        globalConfig = {
            stubs: {
                'custom-loader': true,
                'alert': true,
                'text-field': true,
            },
            mocks: {
                lang: (msg) => msg,
                basePath: () => '/admin',
            }
        };

        // Mock window.location
        delete window.location;
        window.location = { pathname: '/admin/whitelist/add' };
    });

    it('renders add mode correctly', () => {
        const wrapper = mount(WhiteListCreate, {
            global: globalConfig,
        });

        expect(wrapper.vm.title).toBe('add_new_whitelist_ip');
        expect(wrapper.find('h3').text()).toBe('add_new_whitelist_ip');
    });

    it('renders edit mode and fetches data', async () => {
        window.location.pathname = '/admin/whitelist/1/edit';
        const wrapper = mount(WhiteListCreate, {
            global: globalConfig,
        });

        await new Promise(resolve => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.title).toBe('edit_whitelist');
        expect(axios.get).toHaveBeenCalledWith('/api/admin/whitelist-edit/1');
        expect(wrapper.vm.whitelist_host_ip).toBe('127.0.0.1');
    });

    it('updates data on change', () => {
        const wrapper = mount(WhiteListCreate, {
            global: globalConfig,
        });

        wrapper.vm.onChange('192.168.1.1', 'whitelist_host_ip');
        expect(wrapper.vm.whitelist_host_ip).toBe('192.168.1.1');
    });
});
