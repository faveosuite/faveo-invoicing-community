import { mount } from '@vue/test-utils';
import Sidebar from '../../../Resources/js/Layouts/Components/Sidebar.vue';
import { useStore } from 'vuex';
import axios from 'axios';

jest.mock('vuex');
jest.mock('axios');

describe('Sidebar.vue', () => {
    let store;
    let getters;

    beforeEach(() => {
        jest.useFakeTimers();
        getters = {
            getAdminData: 'logo.png'
        };
        store = {
            getters: getters
        };
        useStore.mockReturnValue(store);

        axios.get.mockResolvedValue({
            data: {
                navigations: [
                    { name: 'Dashboard', routeString: '/dashboard', iconClass: 'fa-dashboard' }
                ]
            }
        });
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    const mountComponent = () => {
        return mount(Sidebar, {
            global: {
                mocks: {
                    $route: { path: '/dashboard' }
                },
                stubs: {
                    'navigation': true,
                    'image-element': true,
                    'loader': true
                }
            }
        });
    };

    it('renders the admin logo', () => {
        const wrapper = mountComponent();
        const imageElement = wrapper.findComponent({ name: 'image-element' });
        expect(imageElement.props('sourceUrl')).toBe('logo.png');
    });

    it('fetches routes on mount and hides loader after timeout', async () => {
        const wrapper = mountComponent();

        expect(wrapper.vm.loading).toBe(true);
        expect(axios.get).toHaveBeenCalledWith('/json/routes.json');

        // Flush microtasks so axios .then() runs and schedules the setTimeout
        await wrapper.vm.$nextTick();
        // Now advance the fake setTimeout(fn, 1000) timer
        jest.advanceTimersByTime(1000);
        await wrapper.vm.$nextTick();

        expect(wrapper.vm.loading).toBe(false);
        expect(wrapper.vm.navigations.length).toBe(1);
    });

    it('renders navigation components after loading', async () => {
        const wrapper = mountComponent();

        await wrapper.vm.$nextTick();
        jest.advanceTimersByTime(1000);
        await wrapper.vm.$nextTick();

        const navigations = wrapper.findAllComponents({ name: 'navigation' });
        expect(navigations.length).toBe(1);
        expect(navigations[0].props('menuItem')).toEqual({
            name: 'Dashboard', routeString: '/dashboard', iconClass: 'fa-dashboard'
        });
    });

    it('increments counter on route change', async () => {
        const wrapper = mountComponent();
        const initialCounter = wrapper.vm.counter;

        // Simulate route change by calling the watcher
        await wrapper.vm.$options.watch.$route.call(wrapper.vm, { path: '/new-path' }, { path: '/old-path' });
        
        expect(wrapper.vm.counter).toBe(initialCounter + 1);
    });
});
