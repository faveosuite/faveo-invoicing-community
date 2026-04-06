import { mount } from '@vue/test-utils';
import Navigation from '../../../Resources/js/Layouts/Components/Navigation.vue';

describe('Navigation.vue', () => {
    let propsData;
    let route;
    let router;

    beforeEach(() => {
        propsData = {
            menuItem: {
                name: 'Dashboard',
                iconClass: 'fa-dashboard',
                routeString: '/dashboard',
                hasChildren: false,
                children: []
            }
        };

        route = { path: '/dashboard' };
        router = {
            replace: jest.fn(),
            afterEach: jest.fn()
        };
    });

    const mountComponent = (props = propsData) => {
        return mount(Navigation, {
            props,
            global: {
                mocks: {
                    $route: route,
                    $router: router,
                    basePath: () => '/base'
                },
                stubs: {
                    'router-link': { template: '<a><slot></slot></a>' }
                },
                directives: {
                    tooltip: {}
                }
            }
        });
    };

    it('renders the menu item name and icon', () => {
        const wrapper = mountComponent();
        expect(wrapper.text()).toContain('Dashboard');
        expect(wrapper.find('i.fa-dashboard').exists()).toBe(true);
    });

    it('identifies if it is expandable based on children', () => {
        const wrapper = mountComponent();
        expect(wrapper.vm.isExpandable).toBe(false);

        const expandableItem = {
            ...propsData.menuItem,
            hasChildren: true,
            children: [{ name: 'Sub', routeString: '/sub', iconClass: 'fa-sub' }]
        };
        const expandableWrapper = mountComponent({ menuItem: expandableItem });
        expect(expandableWrapper.vm.isExpandable).toBe(true);
    });

    it('toggles menu when handleMainMenuAction is called on expandable item', async () => {
        const expandableItem = {
            ...propsData.menuItem,
            hasChildren: true,
            children: [{ name: 'Sub', routeString: '/sub', iconClass: 'fa-sub' }]
        };
        const wrapper = mountComponent({ menuItem: expandableItem });
        
        await wrapper.find('a.nav-link').trigger('click');
        expect(wrapper.vm.isMenuExtended).toBe(true);
        
        await wrapper.find('a.nav-link').trigger('click');
        expect(wrapper.vm.isMenuExtended).toBe(false);
    });

    it('calls router.replace when non-expandable item is clicked', async () => {
        const wrapper = mountComponent();
        await wrapper.find('a.nav-link').trigger('click');
        expect(router.replace).toHaveBeenCalledWith('/dashboard');
    });

    it('sets active status correctly based on route', () => {
        route.path = '/dashboard';
        const wrapper = mountComponent();
        expect(wrapper.vm.isMainActive).toBe(true);

        const expandableItem = {
            ...propsData.menuItem,
            hasChildren: true,
            children: [{ name: 'Sub', routeString: '/sub', iconClass: 'fa-sub' }]
        };
        route.path = '/sub';
        const wrapper2 = mountComponent({ menuItem: expandableItem });
        expect(wrapper2.vm.isOneOfChildrenActive).toBe(true);
        expect(wrapper2.vm.isMenuExtended).toBe(true);
    });
});
