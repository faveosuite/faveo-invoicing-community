import { onBeforeUnmount, onMounted } from 'vue'

const DEBOUNCE_MS = 1000

function parseOptions(raw) {
    if (!raw) return {}
    try { return JSON.parse(raw.replace(/'/g, '"')) } catch { return {} }
}

export function useCarouselLight(selector = '.owl-carousel-light') {
    const instances = []

    onMounted(() => {
        document.querySelectorAll(selector).forEach(el => {
            instances.push(new CarouselLight(el))
        })
    })

    onBeforeUnmount(() => {
        instances.forEach(inst => inst.destroy())
        instances.length = 0
    })
}

class CarouselLight {
    constructor(el) {
        if (el.__carouselLight) return
        el.__carouselLight = this

        this.el = el
        this.clickFlag = true
        this.autoPlayTimer = null
        this._handlers = []

        const opts = parseOptions(el.getAttribute('data-plugin-options'))
        this.options = {
            autoplay:               opts.autoplay !== false,
            autoplayTimeout:        opts.autoplayTimeout ?? 7000,
            disableAutoPlayOnClick: opts.disableAutoPlayOnClick !== false,
            swipeEvents:            opts.swipeEvents !== false,
        }

        this._build()
        this._setupNav()
        this._setupDots()
        this._autoPlay()
        this._carouselNavigate()
        if (this.options.swipeEvents) this._setupSwipe()
    }

    _items() { return Array.from(this.el.querySelectorAll('.owl-item')) }
    _active() { return this.el.querySelector('.owl-item.active') }

    _build() {
        // owl-loaded required by owl.carousel.css: .owl-carousel { display:none }
        // .owl-carousel.owl-loaded { display:block }
        this.el.classList.add('owl-loaded')
        this.el.style.opacity = '1'

        const first = this.el.querySelector('.owl-item')
        if (first) first.classList.add('active')

        // Fire before nav/dots setup — matches theme.js PluginCarouselLight line 1723
        this.el.dispatchEvent(new CustomEvent('initialized.owl.carousel'))
    }

    _on(target, type, handler, opts) {
        target.addEventListener(type, handler, opts)
        this._handlers.push({ target, type, handler })
    }

    _setupNav() {
        const prev = this.el.querySelector('.owl-prev')
        const next = this.el.querySelector('.owl-next')
        if (prev) this._on(prev, 'click', e => {
            e.preventDefault()
            if (this.options.disableAutoPlayOnClick) this._stopAutoplay()
            if (!this._debounce()) this._prev()
        })
        if (next) this._on(next, 'click', e => {
            e.preventDefault()
            if (this.options.disableAutoPlayOnClick) this._stopAutoplay()
            if (!this._debounce()) this._next()
        })
    }

    _setupDots() {
        this.el.querySelectorAll('.owl-dot').forEach((dot, i) => {
            this._on(dot, 'click', e => {
                e.preventDefault()
                if (dot.classList.contains('active')) return
                if (this.options.disableAutoPlayOnClick) this._stopAutoplay()
                if (!this._debounce()) {
                    const items = this._items()
                    if (items[i]) this._changeSlide(items[i])
                }
            })
        })
    }

    _prev() {
        const active = this._active()
        const prev   = active?.previousElementSibling
        const items  = this._items()
        this._changeSlide(prev?.classList.contains('owl-item') ? prev : items[items.length - 1])
    }

    _next() {
        const active = this._active()
        const next   = active?.nextElementSibling
        const items  = this._items()
        this._changeSlide(next?.classList.contains('owl-item') ? next : items[0])
    }

    _changeSlide(next) {
        if (!next) return
        const items   = this._items()
        const prev    = this._active()
        const prevIdx = prev ? items.indexOf(prev) : -1
        const nextIdx = items.indexOf(next)

        // Fade out current — matches theme.js lines 1734-1738
        prev?.classList.add('removing')
        prev?.classList.remove('fadeIn')
        prev?.classList.add('fadeOut', 'animated')

        this.el.dispatchEvent(new CustomEvent('change.owl.carousel', {
            detail: { nextSlideIndex: nextIdx, prevSlideIndex: prevIdx }
        }))

        // Fade in next — matches theme.js lines 1742-1749
        setTimeout(() => {
            setTimeout(() => prev?.classList.remove('active', 'removing', 'fadeOut', 'animated'), 400)
            next.classList.add('active')
            next.classList.remove('fadeOut')
            next.classList.add('fadeIn', 'animated')
        }, 200)

        // Sync dot active state
        this.el.querySelectorAll('.owl-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === nextIdx)
        })

        setTimeout(() => {
            this.el.dispatchEvent(new CustomEvent('changed.owl.carousel', {
                detail: { nextSlideIndex: nextIdx, prevSlideIndex: prevIdx }
            }))
        }, 500)
    }

    _debounce() {
        if (!this.clickFlag) return true
        this.clickFlag = false
        setTimeout(() => { this.clickFlag = true }, DEBOUNCE_MS)
        return false
    }

    _autoPlay() {
        if (this.options.autoplay) {
            this.autoPlayTimer = setInterval(() => this._next(), this.options.autoplayTimeout)
        }
    }

    _stopAutoplay() {
        clearInterval(this.autoPlayTimer)
        this.autoPlayTimer = null
    }

    // data-carousel-navigate-id — matches theme.js PluginCarouselLight.carouselNavigate()
    _carouselNavigate() {
        const id = this.el.id
        if (!id) return

        document.querySelectorAll(`[data-carousel-navigate-id="#${id}"]`).forEach(btn => {
            const toIndex = parseInt(btn.dataset.carouselNavigateTo, 10)
            this._on(btn, 'click', () => {
                if (this.options.disableAutoPlayOnClick) this._stopAutoplay()
                const items = this._items()
                if (items[toIndex - 1]) this._changeSlide(items[toIndex - 1])
            })
        })

        this._on(this.el, 'change.owl.carousel', () => {
            document.querySelectorAll(`[data-carousel-navigate-id="#${id}"]`)
                .forEach(btn => btn.classList.remove('active'))
        })

        this._on(this.el, 'changed.owl.carousel', e => {
            const idx = e.detail?.nextSlideIndex
            if (idx != null) {
                document.querySelector(
                    `[data-carousel-navigate-id="#${id}"][data-carousel-navigate-to="${idx + 1}"]`
                )?.classList.add('active')
            }
        })
    }

    _setupSwipe() {
        let startX = null
        this._on(this.el, 'touchstart', e => { startX = e.touches[0].clientX }, { passive: true })
        this._on(this.el, 'touchend', e => {
            if (startX === null) return
            const dx = e.changedTouches[0].clientX - startX
            if (Math.abs(dx) > 40) dx > 0 ? this._prev() : this._next()
            startX = null
        })
    }

    destroy() {
        this._stopAutoplay()
        this._handlers.forEach(({ target, type, handler }) => target.removeEventListener(type, handler))
        this._handlers = []
        this.el.classList.remove('owl-loaded')
        delete this.el.__carouselLight
    }
}
