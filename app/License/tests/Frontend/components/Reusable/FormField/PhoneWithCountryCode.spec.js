import { mount } from '@vue/test-utils';
import PhoneWithCountryCode from '../../../../../../Resources/js/components/Reusable/FormField/PhoneWithCountryCode.vue';
import axios from 'axios';
import intlTelInput from "intl-tel-input/intlTelInputWithUtils";

jest.mock('axios');
jest.mock('intl-tel-input/intlTelInputWithUtils');

describe('PhoneWithCountryCode.vue', () => {
    let wrapper;
    const mockIti = {
        destroy: jest.fn(),
        isValidNumber: jest.fn(() => true),
        getSelectedCountryData: jest.fn(() => ({ dialCode: '91', iso2: 'in' })),
        getSelectedCountry: jest.fn(() => 'in'),
        setCountry: jest.fn(),
        options: { initialCountry: 'auto' }
    };

    const props = {
        value: '1234567890',
        name: 'phone',
        onChange: jest.fn(),
        countryCode: '91',
        countryIso: 'in'
    };

    beforeEach(() => {
        intlTelInput.mockReturnValue(mockIti);
        intlTelInput.getCountryData = jest.fn(() => [{ dialCode: '91', iso2: 'in' }]);
        axios.get.mockResolvedValue({ data: { country_code: 'IN' } });

        wrapper = mount(PhoneWithCountryCode, {
            props,
            global: {
                mocks: {
                    lang: (s) => s,
                    trans: (s) => s
                },
                stubs: {
                    'form-field-template': {
                        template: '<div><slot /></div>'
                    }
                }
            }
        });
    });

    afterEach(() => {
        jest.clearAllMocks();
    });

    it('renders the input field', () => {
        expect(wrapper.find('input[type="tel"]').exists()).toBe(true);
    });

    it('initializes intlTelInput on mount', () => {
        expect(intlTelInput).toHaveBeenCalled();
    });

    it('calls onChange when input value changes', async () => {
        const input = wrapper.find('input');
        await input.setValue('9876543210');
        expect(props.onChange).toHaveBeenCalledWith('9876543210', 'phone');
    });

    it('validates phone number on value change', async () => {
        mockIti.isValidNumber.mockReturnValue(false);
        wrapper.vm.isMounted = true; // bypass the early-return guard
        await wrapper.setProps({ value: 'invalid' });
        expect(wrapper.vm.showError).toBe(true);
        expect(wrapper.vm.errorMsg).toBe('invalid_phone_number');
        expect(wrapper.emitted().validPhoneNumber[0]).toEqual(['phone', false]);
    });

    it('destroys intlTelInput on unmount', () => {
        wrapper.unmount();
        expect(mockIti.destroy).toHaveBeenCalled();
    });
});
