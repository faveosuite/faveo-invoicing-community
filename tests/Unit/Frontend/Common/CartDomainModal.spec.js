import { mount } from '@vue/test-utils'
import CartDomainModal from '@/themes/porto/components/cart/CartDomainModal.vue'
import ActionButton from '@/components/Reusable/ActionButton.vue'

// Minimal ClientField: an input plus its error text.
const ClientField = {
    props: ['name', 'modelValue', 'error'],
    emits: ['update:modelValue'],
    template: `<div><input :name="name" :value="modelValue" @input="$emit('update:modelValue', $event.target.value)"><span class="err">{{ error }}</span></div>`,
}

const items = [{ id: 1, name: 'A', domain: null }, { id: 2, name: 'B', domain: 'b.example.com' }]

async function open() {
    const wrapper = mount(CartDomainModal, {
        props: { show: false, items },
        global: { stubs: { teleport: true }, components: { ClientField, ActionButton } },
    })
    await wrapper.setProps({ show: true })
    return wrapper
}

const errorTexts = (w) => w.findAll('.err').map(e => e.text())

describe('CartDomainModal.vue', () => {
    it('opens with saved domains prefilled and no errors', async () => {
        const w = await open()
        expect(w.find('input[name="domain_2"]').element.value).toBe('b.example.com')
        expect(errorTexts(w).every(t => t === '')).toBe(true)
    })

    it('shows errors only after submit with an empty or invalid domain', async () => {
        const w = await open()
        await w.find('button.btn-primary').trigger('click')
        expect(errorTexts(w)[0]).not.toBe('')
        expect(w.emitted('save')).toBeUndefined()

        await w.find('input[name="domain_1"]').setValue('http://a.com')
        await w.find('button.btn-primary').trigger('click')
        expect(errorTexts(w)[0]).not.toBe('')
    })

    it('emits save with trimmed domains keyed by item id', async () => {
        const w = await open()
        await w.find('input[name="domain_1"]').setValue(' a.example.com ')
        await w.find('button.btn-primary').trigger('click')
        expect(w.emitted('save')[0][0]).toEqual({ 1: 'a.example.com', 2: 'b.example.com' })
    })
})
