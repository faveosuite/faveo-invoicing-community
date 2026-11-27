import { mount } from '@vue/test-utils'
import CartQtyStepper from '@/themes/porto/components/cart/CartQtyStepper.vue'

describe('CartQtyStepper.vue', () => {
    it('stops at its limits on both sides', () => {
        const w = mount(CartQtyStepper, { props: { value: 9999, min: 1, max: 9999, label: 'Agents' } })
        const [minus, plus] = w.findAll('button')
        expect(plus.attributes('disabled')).toBeDefined()
        expect(minus.attributes('disabled')).toBeUndefined()
        expect(plus.attributes('aria-label')).toBe('message.increase Agents')
    })
})
