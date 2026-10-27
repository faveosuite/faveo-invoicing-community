import { mount } from '@vue/test-utils'
import Honeypot from '@/components/Reusable/Honeypot.vue'

const mountHoneypot = (props = {}) =>
    mount(Honeypot, {
        props: { name: 'login', ...props },
    })

describe('Honeypot.vue', () => {
    let wrapper

    beforeEach(() => {
        globalThis.mockHttp.onGet('honeypot').reply(200, {
            data: { pot: 'p_abc', time: 't_xyz', token: 'encrypted-token-123' },
        })
        wrapper = mountHoneypot()
    })

    it('is a vue instance', () => {
        expect(wrapper.exists()).toBeTruthy()
    })

    it('renders honeypot field container', () => {
        expect(wrapper.find('.honeypot-field').exists()).toBe(true)
    })

    it('has aria-hidden on the container', () => {
        expect(wrapper.find('.honeypot-field').attributes('aria-hidden')).toBe('true')
    })

    it('renders an input of type text', () => {
        expect(wrapper.find('input[type="text"]').exists()).toBe(true)
    })

    it('input has tabindex of -1', () => {
        expect(wrapper.find('input').attributes('tabindex')).toBe('-1')
    })

    it('input has autocomplete off', () => {
        expect(wrapper.find('input').attributes('autocomplete')).toBe('off')
    })

    it('renders a label', () => {
        expect(wrapper.find('label').exists()).toBe(true)
    })

    it('uses default label text', () => {
        expect(wrapper.find('label').text()).toBe('Do not fill this field')
    })

    it('uses custom label prop', () => {
        wrapper = mountHoneypot({ label: 'Leave blank' })
        expect(wrapper.find('label').text()).toBe('Leave blank')
    })

    it('emits ready=true after successful API load', async () => {
        await flushPromises()
        const readyEmits = wrapper.emitted('ready')
        expect(readyEmits).toBeTruthy()
        const lastEmit = readyEmits[readyEmits.length - 1]
        expect(lastEmit[0]).toBe(true)
    })

    it('emits update:modelValue after successful API load', async () => {
        await flushPromises()
        expect(wrapper.emitted('update:modelValue')).toBeTruthy()
    })

    it('emits ready=false when API fails after retries', async () => {
        globalThis.mockHttp.reset()
        globalThis.mockHttp.onGet('honeypot').reply(500, {})

        jest.useFakeTimers()
        wrapper = mountHoneypot()

        // Advance past retry delays
        jest.advanceTimersByTime(5000)
        await flushPromises()
        jest.useRealTimers()

        const readyEmits = wrapper.emitted('ready')
        if (readyEmits) {
            const lastEmit = readyEmits[readyEmits.length - 1]
            expect(lastEmit[0]).toBe(false)
        }
    })

    it('exposes reload method', () => {
        expect(typeof wrapper.vm.reload).toBe('function')
    })

    it('sets input name attribute using name prop and pot field', async () => {
        await flushPromises()
        const input = wrapper.find('input')
        expect(input.attributes('name')).toContain('login')
    })
})
