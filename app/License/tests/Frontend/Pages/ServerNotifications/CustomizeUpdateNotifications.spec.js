import { mount } from '@vue/test-utils';
import CustomizeUpdateNotifications from '../../../../../Resources/js/Pages/ServerNotifications/CustomizeUpdateNotifications.vue';
import axios from 'axios';

jest.mock('axios');

describe('CustomizeUpdateNotifications.vue', () => {
    let globalConfig;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    notification_operation_ok: 'Operation OK',
                    // ... other fields
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
                trans: (msg) => msg,
            }
        };
    });

    it('renders the component and fetches initial values', async () => {
        const wrapper = mount(CustomizeUpdateNotifications, {
            global: globalConfig,
        });

        expect(axios.get).toHaveBeenCalledWith('/api/admin/showUpdateNotifications');
        
        await wrapper.vm.$nextTick(); 
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.hasDataPopulated).toBe(true);
        expect(wrapper.find('.card-title').text()).toBe('customize_update_notifications');
    });

    it('shows loader when loading is true', async () => {
        axios.get.mockImplementation(() => new Promise(() => {})); // never resolves, keeps loading = true
        const wrapper = mount(CustomizeUpdateNotifications, {
            global: globalConfig,
        });
        await wrapper.vm.$nextTick();
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('updates data on input change', async () => {
        const wrapper = mount(CustomizeUpdateNotifications, {
            global: globalConfig,
        });
        
        await wrapper.setData({ hasDataPopulated: true });
        wrapper.vm.onChange('New Message', 'notification_operation_ok');
        expect(wrapper.vm.notification_operation_ok).toBe('New Message');
    });

    it('calls onSubmit when update button is clicked', async () => {
        axios.post.mockResolvedValue({ data: { message: 'Success' } });
        const wrapper = mount(CustomizeUpdateNotifications, {
            global: globalConfig,
        });
        
        await wrapper.setData({ 
            hasDataPopulated: true,
            notification_id: 1,
            notification_operation_ok: 'test'
        });

        await wrapper.find('button.btn-primary').trigger('click');
        expect(axios.post).toHaveBeenCalled();
    });
});
