import { mount } from '@vue/test-utils';
import DynamicSelect1 from '../../../../../../Resources/js/components/Reusable/FormField/DynamicSelect1.vue';
import axios from 'axios';

jest.mock('axios');
jest.mock('../../../../../../Resources/js/helpers/responseHandler', () => ({
    errorHandler: jest.fn()
}));

// Mock IntersectionObserver
global.IntersectionObserver = class IntersectionObserver {
    constructor() {}
    observe() {}
    unobserve() {}
    disconnect() {}
};

// Mock window.eventHub
window.eventHub = {
    $on: jest.fn(),
    $off: jest.fn(),
    $emit: jest.fn()
};

describe('DynamicSelect1.vue (dynamic-select)', () => {
    const props = {
        name: 'dynamic_select_1',
        onChange: jest.fn(),
        elements: [
            { id: 1, name: 'Option 1' },
            { id: 2, name: 'Option 2' }
        ],
        label: 'Dynamic Label'
    };

    beforeEach(() => {
        jest.useFakeTimers();
    });

    afterEach(() => {
        jest.useRealTimers();
    });

    it('renders the label after timeout', async () => {
        const wrapper = mount(DynamicSelect1, {
            props,
            global: {
                stubs: {
                    'form-field-template': {
                        template: '<div><label>{{label}}</label><slot /></div>',
                        props: ['label']
                    },
                    'v-select': true,
                    'loader': true,
                    'faveo-image-element': true
                }
            }
        });

        expect(wrapper.vm.showField).toBe(false);
        jest.advanceTimersByTime(1);
        await wrapper.vm.$nextTick();
        expect(wrapper.vm.showField).toBe(true);
        expect(wrapper.text()).toContain('Dynamic Label');
    });

    it('calls onChange when value changes', async () => {
        const wrapper = mount(DynamicSelect1, {
            props,
            global: {
                stubs: {
                    'form-field-template': true,
                    'v-select': true,
                    'loader': true,
                    'faveo-image-element': true
                }
            }
        });

        jest.advanceTimersByTime(1);
        await wrapper.vm.onValueChange({ id: 1, name: 'Option 1' });
        expect(props.onChange).toHaveBeenCalledWith({ id: 1, name: 'Option 1' }, 'dynamic_select_1');
    });

    it('fetches data from API when apiEndpoint is provided', async () => {
        const apiData = {
            data: {
                data: [
                    { id: 3, name: 'Option 3' }
                ],
                next_page_url: null
            }
        };
        axios.get.mockResolvedValue({ data: apiData });

        const wrapper = mount(DynamicSelect1, {
            props: {
                ...props,
                apiEndpoint: '/api/test'
            },
            global: {
                stubs: {
                    'form-field-template': true,
                    'v-select': true,
                    'loader': true,
                    'faveo-image-element': true
                }
            }
        });

        jest.advanceTimersByTime(1);
        await wrapper.vm.$nextTick();
        // IntersectionObserver never fires in tests; trigger the API call directly
        await wrapper.vm.getListFromServer(true);
        expect(axios.get).toHaveBeenCalledWith('/api/test', expect.any(Object));
    });
});
