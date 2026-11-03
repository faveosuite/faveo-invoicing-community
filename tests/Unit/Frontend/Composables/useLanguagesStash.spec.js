jest.mock('@/plugins/axios', () => ({ __esModule: true, default: { post: jest.fn(() => Promise.resolve({})) } }))

import { useLanguages, restoreStashedForm } from '@/themes/porto/composables/useLanguages.js'
import { createTestingPinia } from '@pinia/testing'
import { setActivePinia } from 'pinia'

// Language switch reloads the page: typed text survives, passwords never do.
describe('useLanguages form stash', () => {
    beforeEach(() => {
        setActivePinia(createTestingPinia())
        sessionStorage.clear()
        Object.defineProperty(window, 'location', { writable: true, value: { ...window.location, pathname: '/register', reload: jest.fn() } })
        document.body.innerHTML = `<div id="app-client" data-languages="[]"></div><main id="main-content">
            <input name="first_name" value="Jane"><input name="email" type="email" value="j@x.com">
            <input name="password" type="password" value="Secret@1"><input name="pot" value=""></main>`
    })

    it('restores text fields after the reload, but not passwords', async () => {
        await useLanguages().selectLang({ locale: 'de' })

        // "Reload": same page, fresh empty form.
        document.querySelectorAll('#main-content input').forEach(i => { i.value = '' })
        const seen = []
        document.querySelector('[name=first_name]').addEventListener('input', () => seen.push('first_name'))
        restoreStashedForm()

        expect(document.querySelector('[name=first_name]').value).toBe('Jane')
        expect(document.querySelector('[name=email]').value).toBe('j@x.com')
        expect(document.querySelector('[name=password]').value).toBe('')
        expect(seen).toEqual(['first_name']) // fires input so v-model picks it up
        expect(sessionStorage.getItem('lang-switch-form')).toBeNull() // one-shot
    })

    it('does not restore onto a different page', async () => {
        await useLanguages().selectLang({ locale: 'de' })
        document.querySelectorAll('#main-content input').forEach(i => { i.value = '' })
        window.location.pathname = '/login'
        restoreStashedForm()
        expect(document.querySelector('[name=first_name]').value).toBe('')
    })
})
