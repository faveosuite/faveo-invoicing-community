import { mount } from '@vue/test-utils';
import Loader from '../../../../../Resources/js/components/Reusable/Loader.vue';

describe('Loader.vue', () => {
    it('renders the spinner with correct props', () => {
        const wrapper = mount(Loader, {
            props: {
                size: 80,
                color: '#ff0000',
                opacity: 0.5,
                isFullPage: true,
            },
            global: {
                stubs: {
                    spinner: true,
                },
            },
        });

        const spinner = wrapper.find('spinner-stub');
        expect(spinner.exists()).toBe(true);
        expect(spinner.attributes('width')).toBe('80');
        expect(spinner.attributes('height')).toBe('80');
        expect(spinner.attributes('color')).toBe('#ff0000');
        expect(spinner.attributes('opacity')).toBe('0.5');
        expect(spinner.attributes('is-full-page') ?? 'true').toBe('true');
    });
});
