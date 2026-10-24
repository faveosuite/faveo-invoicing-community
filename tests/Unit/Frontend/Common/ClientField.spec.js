jest.mock('@/plugins/i18n', () => ({ __: (key) => key }))

import { mount } from '@vue/test-utils'
import ClientField from '@/themes/porto/components/forms/ClientField.vue'

describe('ClientField.vue digits-only', () => {
    it('strips non-digits from the box itself and caps the length', async () => {
        const wrapper = mount(ClientField, { props: { name: 'otp', digitsOnly: true, maxlength: 6 } })
        const input = wrapper.find('input')

        expect(input.attributes('maxlength')).toBe('6')
        await input.setValue('12ab3!4')

        expect(input.element.value).toBe('1234')
        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['1234'])
    })

    it('leaves other fields untouched', async () => {
        const wrapper = mount(ClientField, { props: { name: 'name' } })
        await wrapper.find('input').setValue('ab 12')

        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['ab 12'])
    })
})
