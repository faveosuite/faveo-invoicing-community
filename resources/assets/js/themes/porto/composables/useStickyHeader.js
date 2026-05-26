import { onBeforeUnmount, onMounted } from 'vue'

const MOBILE_BREAKPOINT = 992

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

        const opts = parseOptions(header.getAttribute('data-plugin-options'))
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

        const setSpacerHeight = () => {
            header.style.height = `${headerBody.offsetHeight}px`
        }

        const updateSticky = () => {
            if (!enableOnMobile && window.innerWidth < MOBILE_BREAKPOINT) {
                html.classList.remove('sticky-header-active')
                return
            }
            html.classList.toggle('sticky-header-active', window.scrollY >= startAt)
        }

        setSpacerHeight()
        updateSticky()

        scrollHandler = updateSticky
        resizeHandler = () => {
            setSpacerHeight()
            updateSticky()
        }

        window.addEventListener('scroll', scrollHandler, { passive: true })
        window.addEventListener('resize', resizeHandler)
    })

    onBeforeUnmount(() => {
        if (scrollHandler) window.removeEventListener('scroll', scrollHandler)
        if (resizeHandler) window.removeEventListener('resize', resizeHandler)
        const html = document.documentElement
        html.classList.remove('sticky-header-enabled', 'sticky-header-active')
        if (effectClass) html.classList.remove(effectClass)
    })
}
