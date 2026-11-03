import { mount } from '@vue/test-utils'
import PasswordHints from '@/pages/client/auth/partials/PasswordHints.vue'

const mountHints = (password, props = {}) => mount(PasswordHints, { props: { password, show: true, ...props } })
const filled = (w) => w.findAll('.d-flex.gap-1 > span').filter(s => !s.classes('bg-body-secondary'))

describe('PasswordHints.vue', () => {
    it('renders nothing when hidden', () => {
        expect(mountHints('abc', { show: false }).find('.popover').exists()).toBe(false)
    })

    it('lists all 5 rules and marks the ones met', () => {
        const items = mountHints('abcdefgh').findAll('li')
        expect(items).toHaveLength(5)
        expect(items.filter(li => li.classes('text-success'))).toHaveLength(2) // length + lower
        expect(items.filter(li => li.classes('text-body-secondary'))).toHaveLength(3) // unmet = grey
    })

    it('turns unmet rules red in strict mode (after a failed submit)', () => {
        const items = mountHints('abcdefgh', { strict: true }).findAll('li')
        expect(items.filter(li => li.classes('text-danger'))).toHaveLength(3)
    })

    it.each([
        ['abc',       'message.pwd_strength_weak',   1, 'bg-danger'],
        ['Abcdefgh',  'message.pwd_strength_fair',   2, 'bg-warning'],
        ['Abcdefg1',  'message.pwd_strength_good',   3, 'bg-color-primary'],
        ['Abcdefg1!', 'message.pwd_strength_strong', 4, 'bg-success'],
    ])('rates %s as %s with %i segments filled', (password, label, count, bg) => {
        const w = mountHints(password)
        expect(w.find('strong').text()).toBe(label)
        expect(filled(w)).toHaveLength(count)
        filled(w).forEach(s => expect(s.classes()).toContain(bg))
    })
})
