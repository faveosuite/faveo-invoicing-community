import { onMounted, watch } from 'vue'
import { useRoute } from 'vue-router'

const injected = new Set()

function injectScript(script) {
    if (injected.has(script.id)) return
    injected.add(script.id)

    const content = script.script?.trim() ?? ''
    if (!content) return

    // If it contains <script> tags (e.g. GA snippet with HTML), extract and clone each one
    if (/<script[\s>]/i.test(content)) {
        const tmp = document.createElement('div')
        tmp.innerHTML = content
        tmp.querySelectorAll('script').forEach(orig => {
            const el = document.createElement('script')
            if (orig.src) {
                el.src = orig.src
                el.async = orig.async
            } else {
                el.textContent = orig.textContent
            }
            orig.getAttributeNames().forEach(attr => {
                if (attr !== 'src' && attr !== 'async') el.setAttribute(attr, orig.getAttribute(attr))
            })
            document.head.appendChild(el)
        })
        return
    }

    // Plain JS snippet
    const el = document.createElement('script')
    el.textContent = content
    document.head.appendChild(el)
}

export function useAnalyticsScripts() {
    const route = useRoute()
    const el = document.getElementById('app-client')
    const scripts = JSON.parse(el?.dataset?.scripts ?? '[]')

    const everyPage = scripts.filter(s => s.on_every_page)
    const onRegistration = scripts.filter(s => s.on_registration)

    onMounted(() => {
        if (route.meta?.standalone) return
        everyPage.forEach(injectScript)
    })

    watch(() => route.path, path => {
        if (path === '/login') onRegistration.forEach(injectScript)
    }, { immediate: true })
}
