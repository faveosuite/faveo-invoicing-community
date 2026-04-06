import { mount } from '@vue/test-utils';
import LicenseManagerRenderer from '../../../Resources/js/Layouts/LicenseManagerRenderer.vue';
import axios from 'axios';

jest.mock('axios', () => ({
    interceptors: {
        request: { use: jest.fn() },
        response: { use: jest.fn() }
    }
}));

describe('LicenseManagerRenderer.vue', () => {
    let store;
    let propsData;

    beforeEach(() => {
        store = {
            dispatch: jest.fn(),
            state: { progressBarValue: 0 }
        };
        propsData = {
            versioning: '1.0.0',
            userData: { id: 1, name: 'Admin' }
        };
    });

    const mountComponent = (props = propsData) => {
        return mount(LicenseManagerRenderer, {
            props,
            global: {
                mocks: {
                    $store: store,
                    $route: { path: '/test' }
                },
                stubs: {
                    'router-view': true
                }
            }
        });
    };

    it('dispatches setApiKey and setUserInfo on beforeMount', () => {
        mountComponent();
        expect(store.dispatch).toHaveBeenCalledWith('setApiKey');
        expect(store.dispatch).toHaveBeenCalledWith('setUserInfo', propsData.userData);
    });

    it('updates progress bar value from store state', () => {
        store.state.progressBarValue = 50;
        const wrapper = mountComponent();
        const progressBar = wrapper.find('.progress-bar');
        expect(progressBar.attributes('style')).toContain('width: 50%');
    });

    it('dispatches unsetAlert and unsetValidationError on route change', async () => {
        const wrapper = mountComponent();
        
        // Simulate route change by calling the watcher
        await wrapper.vm.$options.watch.$route.call(wrapper.vm, { path: '/new-path' }, { path: '/old-path' });
        
        expect(store.dispatch).toHaveBeenCalledWith('unsetAlert');
        expect(store.dispatch).toHaveBeenCalledWith('unsetValidationError');
    });

    it('sets up axios interceptors on created', () => {
        mountComponent();
        expect(axios.interceptors.request.use).toHaveBeenCalled();
        expect(axios.interceptors.response.use).toHaveBeenCalled();
    });
});
