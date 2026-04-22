import { mount } from '@vue/test-utils';
import RecaptchaField from '../../../../../../Resources/js/components/Reusable/FormField/RecaptchaField.vue';

describe('RecaptchaField.vue', () => {
    const props = {
        siteKeyValue: 'test-site-key',
        captchaVersion: 'v2',
        verifyCaptcha: jest.fn()
    };

    it('renders vue-recaptcha component if siteKey is present', () => {
        const wrapper = mount(RecaptchaField, {
            props,
            global: {
                stubs: {
                    'vue-recaptcha': {
                        template: '<div class="vue-recaptcha-stub"></div>',
                        props: ['sitekey', 'size']
                    }
                }
            }
        });

        expect(wrapper.find('.vue-recaptcha-stub').exists()).toBe(true);
        expect(wrapper.vm.siteKey).toBe('test-site-key');
    });

    it('calls verifyCaptcha when recaptcha is verified', async () => {
        const wrapper = mount(RecaptchaField, {
            props,
            global: {
                stubs: {
                    'vue-recaptcha': true
                }
            }
        });

        await wrapper.vm.markRecaptchaAsVerified('mock-response');
        expect(props.verifyCaptcha).toHaveBeenCalledWith('mock-response');
        expect(wrapper.vm.recaptchaVerified).toBe('mock-response');
    });

    it('calls verifyCaptcha with empty string when recaptcha is expired', async () => {
        const wrapper = mount(RecaptchaField, {
            props,
            global: {
                stubs: {
                    'vue-recaptcha': true
                }
            }
        });

        await wrapper.vm.onExpired();
        expect(props.verifyCaptcha).toHaveBeenCalledWith('');
    });

    it('calls execute on vue-recaptcha when renderMethod is called', async () => {
        const executeMock = jest.fn();
        const wrapper = mount(RecaptchaField, {
            props,
            global: {
                stubs: {
                    'vue-recaptcha': {
                        template: '<div></div>',
                        methods: {
                            execute: executeMock
                        }
                    }
                }
            }
        });

        // Try to mock using defineProperty or prototype, or just spy on the actual method if ref exists
        Object.defineProperty(wrapper.vm.$refs, 'invisibleRecaptcha', {
            get: () => ({ execute: executeMock })
        });

        await wrapper.vm.renderMethod();
        expect(executeMock).toHaveBeenCalled();
    });
});
