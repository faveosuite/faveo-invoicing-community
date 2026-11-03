import { mount } from '@vue/test-utils'
import OtpInput from '@/pages/client/auth/partials/OtpInput.vue'

// Parent-like wrapper so v-model round-trips like on the real pages.
function mountOtp(initial = '') {
    const w = mount({
        components: { OtpInput },
        data: () => ({ code: initial }),
        template: '<OtpInput label="Code" v-model="code" />',
    }, { attachTo: document.body })
    return { w, boxes: () => w.findAll('input'), code: () => w.vm.code }
}

describe('OtpInput.vue', () => {
    it('renders 6 boxes, labelled for screen readers', () => {
        const { boxes } = mountOtp()
        expect(boxes()).toHaveLength(6)
        expect(boxes()[0].attributes('aria-label')).toBe('Code 1/6')
        expect(boxes()[0].attributes('autocomplete')).toBe('one-time-code')
    })

    it('typing a digit fills the box and moves on; letters are ignored', async () => {
        const { boxes, code } = mountOtp()
        await boxes()[0].setValue('4')
        expect(code()).toBe('4')
        expect(document.activeElement).toBe(boxes()[1].element)
        await boxes()[1].setValue('x')
        expect(code()).toBe('4')
    })

    it('typing past the end lands right after the last digit (no gaps)', async () => {
        const { boxes, code } = mountOtp('12')
        await boxes()[5].setValue('3')
        expect(code()).toBe('123')
    })

    it('a pasted or autofilled code fills every box', async () => {
        const { boxes, code } = mountOtp()
        await boxes()[0].trigger('paste', { clipboardData: { getData: () => ' 482-913 ' } })
        expect(code()).toBe('482913')
        await boxes()[0].setValue('123456') // autofill drops the whole code into box 1
        expect(code()).toBe('123456')
    })

    it('Backspace on an empty box clears the previous digit', async () => {
        const { boxes, code } = mountOtp('12')
        await boxes()[2].trigger('keydown', { key: 'Backspace' })
        expect(code()).toBe('1')
    })
})
