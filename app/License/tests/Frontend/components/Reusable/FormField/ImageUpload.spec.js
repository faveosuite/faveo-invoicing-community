import { mount } from '@vue/test-utils';
import ImageUpload from '../../../../../../Resources/js/components/Reusable/FormField/ImageUpload.vue';

// Mock lang from extraLogics
jest.mock('../../../../../../Resources/js/helpers/extraLogics', () => ({
    lang: jest.fn((str) => str),
    trans: jest.fn((str) => str)
}));

describe('ImageUpload.vue', () => {
    const props = {
        label: 'Upload Logo',
        name: 'logo',
        onChange: jest.fn(),
        componentName: 'test_component',
        value: 'default_logo.png'
    };

    it('renders the label and image element', () => {
        const wrapper = mount(ImageUpload, {
            props,
            global: {
                stubs: {
                    'form-field-template': {
                        template: '<div><label>{{label}}</label><slot /></div>',
                        props: ['label']
                    },
                    'image-element': true,
                    'modal': true,
                    'vue-cropper': true
                }
            }
        });

        expect(wrapper.text()).toContain('Upload Logo');
        expect(wrapper.findComponent({ name: 'image-element' }).exists()).toBe(true);
    });

    it('opens file input when change button is clicked', async () => {
        const wrapper = mount(ImageUpload, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' },
                    'image-element': true,
                    'modal': true,
                    'vue-cropper': true
                }
            }
        });

        const fileInput = wrapper.find('input[type="file"]');
        const clickSpy = jest.spyOn(fileInput.element, 'click');
        
        await wrapper.find('button.btn-primary').trigger('click');
        expect(clickSpy).toHaveBeenCalled();
    });

    it('shows modal when showModal is true', async () => {
        const wrapper = mount(ImageUpload, {
            props,
            global: {
                stubs: {
                    'form-field-template': { template: '<div><slot/></div>' },
                    'image-element': true,
                    'modal': {
                        template: '<div><slot name="title" /><slot name="fields" /><slot name="controls" /></div>'
                    },
                    'vue-cropper': true
                }
            }
        });

        await wrapper.setData({ showModal: true });
        expect(wrapper.text()).toContain('Crop Profile');
    });
});
