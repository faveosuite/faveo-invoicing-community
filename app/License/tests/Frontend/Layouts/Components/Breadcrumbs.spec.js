import { mount } from '@vue/test-utils';
import Breadcrumbs from '../../../Resources/js/Layouts/Components/Breadcrumbs.vue';

describe('Breadcrumbs.vue', () => {
    let route;

    beforeEach(() => {
        route = {
            meta: {
                title: 'test_title',
                crumb: {
                    link: { to: '/home', name: 'Home' },
                    root_link: { to: '/dashboard', name: 'Dashboard' },
                    root: 'Root',
                    active: 'Active'
                }
            }
        };
    });

    const mountComponent = () => {
        return mount(Breadcrumbs, {
            global: {
                mocks: {
                    $route: route,
                    trans: (key) => key
                },
                stubs: {
                    'router-link': { template: '<a><slot></slot></a>' }
                }
            }
        });
    };

    it('renders the title correctly', () => {
        const wrapper = mountComponent();
        expect(wrapper.find('h2').text()).toBe('test_title');
    });

    it('renders breadcrumbs when crumb meta is present', () => {
        const wrapper = mountComponent();
        expect(wrapper.find('.breadcrumb').exists()).toBe(true);
        expect(wrapper.text()).toContain('Home');
        expect(wrapper.text()).toContain('Dashboard');
        expect(wrapper.text()).toContain('Root');
        expect(wrapper.text()).toContain('Active');
    });

    it('does not render breadcrumbs when crumb meta is absent', () => {
        route.meta.crumb = null;
        const wrapper = mountComponent();
        expect(wrapper.find('.breadcrumb').exists()).toBe(false);
    });

    it('renders links correctly in breadcrumbs', () => {
        const wrapper = mountComponent();
        const links = wrapper.findAll('a');
        expect(links[0].text()).toBe('Home');
        expect(links[1].text()).toBe('Dashboard');
    });
});
