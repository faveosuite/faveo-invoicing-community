import { mount } from '@vue/test-utils';
import CustomLoader from '../../../../../Resources/js/components/Reusable/CustomLoader.vue';

describe('CustomLoader.vue', () => {
    it('renders the spinner with correct props', () => {
        const wrapper = mount(CustomLoader, {
            props: {
                color: '#00ff00',
                opacity: 0.8,
                isFullPage: false,
            },
            global: {
                stubs: {
                    spinner: true,
                },
            },
        });

        const spinner = wrapper.find('spinner-stub');
        expect(spinner.exists()).toBe(true);
        expect(spinner.attributes('color')).toBe('#00ff00');
        expect(spinner.attributes('opacity')).toBe('0.8');
        expect(spinner.attributes('is-full-page')).toBeFalsy();
    });
});
