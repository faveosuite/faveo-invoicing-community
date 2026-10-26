import { onBeforeUnmount, onMounted, ref } from 'vue'

const MOBILE_BREAKPOINT = 992

export const isStickyActive = ref(false)

function parseOptions(raw) {
    if (!raw) return {}
    try {
        return JSON.parse(raw.replace(/'/g, '"'))
    } catch {
        return {}
    }
}

export function useStickyHeader(headerSelector = '#header') {
    let scrollHandler
    let resizeHandler
    let effectClass = ''

    onMounted(() => {
        const header     = document.querySelector(headerSelector)
        const headerBody = header?.querySelector('.header-body')
        if (!header || !headerBody) return

        const opts = parseOptions(header.dataset.pluginOptions)
        const enabled        = opts.stickyEnabled !== false
        const enableOnMobile = opts.stickyEnableOnMobile === true
        const startAt        = parseInt(opts.stickyStartAt ?? 0, 10) || 0
        const effect         = opts.stickyEffect

        if (!enabled) return

        const html = document.documentElement
        html.classList.add('sticky-header-enabled')
        if (effect) {
            effectClass = `sticky-header-${effect}`
            html.classList.add(effectClass)
        }

        let headerHeight = 0
        const setSpacerHeight = () => {
            headerHeight = headerBody.offsetHeight
            header.style.height = `${headerHeight}px`
        }

        const updateSticky = () => {
            const active = enableOnMobile || globalThis.innerWidth >= MOBILE_BREAKPOINT
                ? globalThis.scrollY >= startAt
                : false
            html.classList.toggle('sticky-header-active', active)
            // Keep anchor/focus scrolling from landing under the fixed header.
            html.style.scrollPaddingTop = active ? `${headerHeight}px` : ''
            isStickyActive.value = active
        }

        setSpacerHeight()
        updateSticky()

        scrollHandler = updateSticky
        resizeHandler = () => {
            setSpacerHeight()
            updateSticky()
        }

        globalThis.addEventListener('scroll', scrollHandler, { passive: true })
        globalThis.addEventListener('resize', resizeHandler)
    })

    onBeforeUnmount(() => {
        if (scrollHandler) globalThis.removeEventListener('scroll', scrollHandler)
        if (resizeHandler) globalThis.removeEventListener('resize', resizeHandler)
        const html = document.documentElement
        html.classList.remove('sticky-header-enabled', 'sticky-header-active')
        html.style.scrollPaddingTop = ''
        if (effectClass) html.classList.remove(effectClass)
        isStickyActive.value = false
    })
}
