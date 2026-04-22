import { mount } from '@vue/test-utils';
import Navbar from '../../../Resources/js/Layouts/Components/Navbar.vue';
import axios from 'axios';

jest.mock('axios');

describe('Navbar.vue', () => {
    let propsData;
    let originalLocation;

    beforeEach(() => {
        propsData = {
            user: {
                client_fname: 'John',
                client_lname: 'Doe',
                client_profile_pic: 'profile.jpg'
            }
        };

        axios.get.mockResolvedValue({ data: {} });
        
        // Mock window.location
        originalLocation = window.location;
        delete window.location;
        window.location = { href: '' };

        // Mock window.axios.defaults.baseURL
        window.axios = {
            defaults: {
                baseURL: 'http://localhost'
            }
        };
    });

    afterEach(() => {
        window.location = originalLocation;
    });

    const mountComponent = (props = propsData) => {
        return mount(Navbar, {
            props,
            global: {
                mocks: {
                    lang: (key) => key,
                    trans: (key) => key,
                    basePath: () => '/base'
                },
                stubs: {
                    'router-link': true,
                    'image-element': true,
                    'custom-loader': true
                },
                directives: {
                    tooltip: {}
                }
            }
        });
    };

    it('renders user information when user prop is provided', () => {
        const wrapper = mountComponent();
        expect(wrapper.text()).toContain('John Doe');
        expect(wrapper.findComponent({ name: 'image-element' }).exists()).toBe(true);
    });

    it('does not render user information when user prop is not provided', () => {
        const wrapper = mountComponent({ user: null });
        expect(wrapper.text()).not.toContain('John Doe');
        expect(wrapper.find('.user-menu').exists()).toBe(false);
    });

    it('calls signOut method and redirects on success', async () => {
        const wrapper = mountComponent();
        await wrapper.vm.signOut();

        expect(wrapper.vm.loading).toBe(false);
        expect(axios.get).toHaveBeenCalledWith('/auth/logout');
        expect(window.location).toBe('http://localhost/login');
    });

    it('renders the base path link for Switch to Billing', () => {
        const wrapper = mountComponent();
        const billingLink = wrapper.find('a[title="Billing"]');
        expect(billingLink.attributes('href')).toBe('/base');
    });
});
