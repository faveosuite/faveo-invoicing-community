import { mount, flushPromises } from '@vue/test-utils'
import { createTestingPinia } from '@pinia/testing'
import { createRouter, createMemoryHistory } from 'vue-router'
import AuthLayout from '@/themes/porto/layouts/AuthLayout.vue'

// Brand panel settings come from client.blade's data-auth-brand; empty values fall back to defaults.
async function mountLayout(brand) {
    const attr = brand === undefined ? '' : ` data-auth-brand='${JSON.stringify(brand)}'`
    document.body.innerHTML = `<div id="app-client" data-base-url="https://x.test"${attr}></div>`
    const router = createRouter({ history: createMemoryHistory(), routes: [{ path: '/', component: { template: '<div/>' } }] })
    await router.push('/')
    const wrapper = mount(AuthLayout, {
        global: { plugins: [createTestingPinia(), router], stubs: { AppAlert: true, loader: true } },
    })
    await flushPromises()
    return wrapper
}

describe('AuthLayout brand panel', () => {
    it('uses the translated defaults and built-in image when nothing is saved', async () => {
        const w = await mountLayout()
        expect(w.find('aside h2').text()).toBe('message.auth_brand_title')
        expect(w.find('aside img').attributes('src')).toContain('/images/auth-brand.svg')
    })

    it('uses saved text and image, falling back per empty field', async () => {
        const w = await mountLayout({ show: true, title: 'Custom title', description: '', tagline: 'My tagline', image: 'https://cdn.test/b.png' })
        expect(w.find('aside h2').text()).toBe('Custom title')
        expect(w.find('aside p').text()).toBe('message.auth_brand_description')
        expect(w.find('aside img').attributes('src')).toBe('https://cdn.test/b.png')
        expect(w.text()).toContain('My tagline')
    })

    it('hides the panel and widens the form column when switched off', async () => {
        const w = await mountLayout({ show: false })
        expect(w.find('aside').exists()).toBe(false)
        expect(w.find('main').classes()).not.toContain('col-lg-5')
    })
})
