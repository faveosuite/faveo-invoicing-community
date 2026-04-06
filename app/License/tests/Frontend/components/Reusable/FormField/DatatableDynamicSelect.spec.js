import { mount } from '@vue/test-utils';
import DatatableDynamicSelect from '../../../../../../Resources/js/components/Reusable/FormField/DatatableDynamicSelect.vue';
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

describe('DatatableDynamicSelect.vue', () => {
    const props = {
        name: 'test_select',
        onChange: jest.fn(),
        elements: [
            { id: 1, name: 'Option 1' },
            { id: 2, name: 'Option 2' }
        ],
        label: 'Test Label'
    };

    it('renders the label and options', () => {
        const wrapper = mount(DatatableDynamicSelect, {
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

        expect(wrapper.text()).toContain('Test Label');
        expect(wrapper.vm.listElements).toHaveLength(2);
    });

    it('calls onChange when value changes', async () => {
        const wrapper = mount(DatatableDynamicSelect, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' },
                    'v-select': true,
                    'loader': true,
                    'faveo-image-element': true
                }
            }
        });

        await wrapper.vm.onValueChange({ id: 1, name: 'Option 1' });
        expect(props.onChange).toHaveBeenCalledWith({ id: 1, name: 'Option 1' }, 'test_select');
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

        const wrapper = mount(DatatableDynamicSelect, {
            props: {
                ...props,
                apiEndpoint: '/api/test'
            },
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' },
                    'v-select': true,
                    'loader': true,
                    'faveo-image-element': true
                }
            }
        });

        // IntersectionObserver never fires in tests; trigger the API call directly
        await wrapper.vm.getListFromServer(true);
        expect(axios.get).toHaveBeenCalledWith('/api/test', expect.any(Object));
    });
});
