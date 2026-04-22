import { mount } from '@vue/test-utils';
import ImageElement from '../../../../../Resources/js/components/Reusable/ImageElement.vue';

describe('ImageElement.vue', () => {
    let propsData;

    beforeEach(() => {
        propsData = {
            id: 'test-img',
            sourceUrl: 'https://example.com/image.png',
            defaultImage: 'default.png',
        };
    });

    const mountComponent = (props = propsData) => {
        return mount(ImageElement, {
            props,
            global: {
                mocks: {
                    basePath: () => '/base',
                },
            },
        });
    };

    it('renders image with correct src from sourceUrl', () => {
        const wrapper = mountComponent();
        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('https://example.com/image.png');
    });

    it('renders default image when sourceUrl is missing', () => {
        propsData.sourceUrl = '';
        const wrapper = mountComponent();
        const img = wrapper.find('img');
        expect(img.attributes('src')).toBe('/base/themes/default/img/default.png');
    });

    it('handles image load error by setting default image', async () => {
        const wrapper = mountComponent();
        
        // Mock the event
        const event = {
            target: {
                src: '',
                onerror: null
            }
        };
        
        await wrapper.vm.onImageLoadError(event);
        expect(event.target.src).toBe('/base/themes/default/img/default.png');
    });
});
