<script setup>
import { provide, ref, onMounted } from 'vue'

const props = defineProps({
    v2SiteKey: { type: String, default: '' },
    v3SiteKey: { type: String, default: '' },
})

function waitForGrecaptcha(timeout = 10000) {
    return new Promise((resolve, reject) => {
        const start = Date.now()
        const check = () => {
            if (window.grecaptcha?.render || window.grecaptcha?.execute) resolve()
            else if (Date.now() - start > timeout) reject(new Error('reCAPTCHA load timeout'))
            else setTimeout(check, 100)
        }
        check()
    })
}

provide('vue-recaptcha-context', {
    isReady: ref(false),
    proxy: {
        async render(el, options) {
            await waitForGrecaptcha()
            return window.grecaptcha.render(el, options)
        },
        reset(widgetId) {
            if (widgetId != null) window.grecaptcha?.reset(widgetId)
        },
        async execute(id, options) {
            if (id == null) return undefined
            await waitForGrecaptcha()
            return window.grecaptcha.execute(id, options)
        },
    },
    options: {
        v2SiteKey: props.v2SiteKey || undefined,
        v3SiteKey: props.v3SiteKey || undefined,
    },
})

onMounted(() => {
    const render = props.v3SiteKey?.trim() || 'explicit'
    const expected = `render=${encodeURIComponent(render)}`
    const existing = document.querySelector('script[src*="google.com/recaptcha/api.js"]')
    if (existing) {
        if (existing.src.includes(expected)) {
            ensureBadgeVisible()
            return
        }
        existing.remove()
        window.grecaptcha = undefined
        document.querySelector('.grecaptcha-badge')?.remove()
    }
    const s = document.createElement('script')
    s.src = `https://www.google.com/recaptcha/api.js?render=${encodeURIComponent(render)}`
    s.async = true
    s.onload = ensureBadgeVisible
    document.head.appendChild(s)
})

function ensureBadgeVisible() {
    window.grecaptcha?.ready?.(() => {
        const badge = document.querySelector('.grecaptcha-badge')
        if (badge) badge.style.display = ''
    })
}
</script>

<template><slot /></template>
