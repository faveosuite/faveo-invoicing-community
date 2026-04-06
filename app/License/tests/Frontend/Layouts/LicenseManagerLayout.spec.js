import { mount } from '@vue/test-utils';
import LicenseManagerLayout from '../../../Resources/js/Layouts/LicenseManagerLayout.vue';
import { useStore } from 'vuex';
import { computed } from 'vue';

jest.mock('vuex');

describe('LicenseManagerLayout.vue', () => {
    let store;
    let getters;

    beforeEach(() => {
        getters = {
            getUserData: { id: 1, name: 'Test User' }
        };
        store = {
            getters: getters
        };
        useStore.mockReturnValue(store);
    });

    const mountComponent = (props = { versioning: '1.0.0' }) => {
        return mount(LicenseManagerLayout, {
            props,
            global: {
                mocks: {
                    $route: { fullPath: '/' }
                },
                stubs: {
                    'nav-bar': true,
                    'side-bar': true,
                    'bread-crumbs': true,
                    'license-footer': true,
                    'router-view': true,
                    'transition': true
                }
            }
        });
    };

    it('renders the layout components', () => {
        const wrapper = mountComponent();
        expect(wrapper.findComponent({ name: 'nav-bar' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'side-bar' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'bread-crumbs' }).exists()).toBe(true);
        expect(wrapper.findComponent({ name: 'license-footer' }).exists()).toBe(true);
    });

    it('passes getUserData to navbar and sidebar', () => {
        const wrapper = mountComponent();
        expect(wrapper.findComponent({ name: 'nav-bar' }).props('user')).toEqual(getters.getUserData);
        expect(wrapper.findComponent({ name: 'side-bar' }).props('user')).toEqual(getters.getUserData);
    });

    it('passes versioning to footer', () => {
        const version = '2.3.4';
        const wrapper = mountComponent({ versioning: version });
        expect(wrapper.findComponent({ name: 'license-footer' }).props('versioning')).toBe(version);
    });
});
