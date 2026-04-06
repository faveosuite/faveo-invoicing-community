import { mount } from '@vue/test-utils';
import CustomizeNotifications from '../../../../../Resources/js/Pages/ServerNotifications/CustomizeNotifications.vue';
import axios from 'axios';

jest.mock('axios');

describe('CustomizeNotifications.vue', () => {
    let globalConfig;

    beforeEach(() => {
        axios.get.mockResolvedValue({
            data: {
                data: {
                    id: 1,
                    notification_product_not_found: 'Product not found',
                    notification_product_inactive: 'Product inactive',
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
        const wrapper = mount(CustomizeNotifications, {
            global: globalConfig,
        });

        expect(axios.get).toHaveBeenCalledWith('/api/admin/viewNotifications');
        
        await wrapper.vm.$nextTick(); // Wait for axios promise
        await wrapper.vm.$nextTick(); // Wait for data updates

        expect(wrapper.vm.hasDataPopulated).toBe(true);
        expect(wrapper.find('.card-title').text()).toBe('customize_notifications');
    });

    it('shows loader when loading is true', async () => {
        axios.get.mockImplementation(() => new Promise(() => {})); // never resolves, keeps loading = true
        const wrapper = mount(CustomizeNotifications, {
            global: globalConfig,
        });
        await wrapper.vm.$nextTick();
        expect(wrapper.find('custom-loader-stub').exists()).toBe(true);
    });

    it('updates data on input change', async () => {
        const wrapper = mount(CustomizeNotifications, {
            global: globalConfig,
        });
        
        await wrapper.setData({ hasDataPopulated: true });
        wrapper.vm.onChange('New Message', 'notification_product_not_found');
        expect(wrapper.vm.notification_product_not_found).toBe('New Message');
    });

    it('calls onSubmit when update button is clicked', async () => {
        axios.post.mockResolvedValue({ data: { message: 'Success' } });
        const wrapper = mount(CustomizeNotifications, {
            global: globalConfig,
        });
        
        await wrapper.setData({ 
            hasDataPopulated: true,
            notification_id: 1,
            notification_product_not_found: 'test',
            // provide all required fields if validation requires them
        });

        // Mock isValid to return true to simplify
        wrapper.vm.isValid = jest.fn().mockReturnValue(true);

        await wrapper.find('button.btn-primary').trigger('click');
        expect(axios.post).toHaveBeenCalled();
    });
});
