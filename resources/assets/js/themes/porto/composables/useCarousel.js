import { onBeforeUnmount, onMounted } from 'vue'

// Matches owl.carousel smartSpeed default (250ms / 1000 = 0.25s)
const TRANSITION_S   = '0.25s'
const TRANSITION_MS  = 250
const SNAP_DELAY_MS  = TRANSITION_MS + 10


// Matches PluginCarousel.defaults from theme.js
const DEFAULTS = {
    loop:                 true,
    items:                1,
    responsive:           { 0: { items: 1 }, 479: { items: 1 }, 768: { items: 2 }, 979: { items: 3 }, 1199: { items: 4 } },
    nav:                  false,
    navText:              [],       // empty → CSS ::before draws FA chevrons
    dots:                 true,
    autoplay:             false,
    autoplayTimeout:      5000,
    autoplayHoverPause:   true,
    margin:               0,
    stagePadding:         0,
    rtl:                  false,
    navHorizontalOffset:  null,
    navVerticalOffset:    null,
    dotsHorizontalOffset: null,
    dotsVerticalOffset:   null,
    refresh:              false,
}

function parseOptions(raw) {
    if (!raw) return {}
    try { return JSON.parse(raw.replace(/'/g, '"')) } catch { return {} }
}

export function useCarousel(selector = '[data-plugin-carousel]:not(.manual), .owl-carousel:not(.manual)') {
    const instances = []

    onMounted(() => {
        document.querySelectorAll(selector).forEach(el => {
            if (el.classList.contains('owl-carousel-light')) return
            if (!el.__carousel) instances.push(new Carousel(el))
        })
    })

    onBeforeUnmount(() => {
        instances.forEach(inst => inst.destroy())
        instances.length = 0
    })
}

class Carousel {
    constructor(el) {
        el.__carousel = this
        this.el             = el
        this._handlers      = []
        this._autoPlayTimer = null
        this._resizeTimer   = null
        this._stagePos      = 0
        this._currentIdx    = 0
        this._cloneOffset   = 0
        this._clonesBefore  = []
        this._clonesAfter   = []
        this._nav           = null
        this._prevBtn       = null
        this._nextBtn       = null
        this._dots          = null
        this._stage         = null
        this._stageOuter    = null
        this._originals     = []
        this._visibleCount  = 1

        const parsed = parseOptions(el.getAttribute('data-plugin-options'))
        this.options = { ...DEFAULTS, ...parsed }

        // RTL from HTML dir attribute — theme.js PluginCarousel.build()
        if (document.documentElement.getAttribute('dir') === 'rtl') this.options.rtl = true

        // items == 1 → clear responsive; items > 4 → add 1199 breakpoint
        if (parsed.items === 1) this.options.responsive = {}
        if (parsed.items > 4) {
            this.options.responsive = { ...this.options.responsive, 1199: { items: parsed.items } }
        }

        this._init()
    }

    // ─── Boot ─────────────────────────────────────────────────────────────────

    _init() {
        const el = this.el

        el.classList.add('owl-theme', 'owl-loading')

        this._originals = Array.from(el.children).filter(
            c => !c.matches('.owl-nav, .owl-dots, .owl-stage-outer')
        )
        if (!this._originals.length) return

        this._originals.forEach(item => item.classList.add('owl-item'))

        // Build .owl-stage-outer > .owl-stage
        this._stage = document.createElement('div')
        this._stage.className = 'owl-stage'

        this._stageOuter = document.createElement('div')
        this._stageOuter.className = 'owl-stage-outer'
        this._stageOuter.style.overflow = 'hidden'      // position:relative from owl.carousel.css

        this._originals.forEach(item => this._stage.appendChild(item))
        this._stageOuter.appendChild(this._stage)
        el.insertBefore(this._stageOuter, el.firstChild)

        this._visibleCount = this._getVisibleCount()
        if (this.options.loop) this._buildClones()

        this._layout(false)

        // Always create nav and dots — disabled class hides via CSS when not needed
        this._buildNav()
        this._buildDots()
        this._applyNavOffsets()
        this._updateNavDisabled()

        if (this.options.autoplay) this._startAutoplay()
        if (this.options.autoplayHoverPause) {
            this._on(el, 'mouseenter', () => this._pauseAutoplay())
            this._on(el, 'mouseleave', () => { if (this.options.autoplay) this._startAutoplay() })
        }

        this._setupSwipe()
        this._carouselNavigate()
        this._handleNavOutside()
        this._handleCenterActiveItem()
        this._handleSvgArrows()

        const onResize = () => {
            clearTimeout(this._resizeTimer)
            this._resizeTimer = setTimeout(() => this._onResize(), 150)
        }
        globalThis.addEventListener('resize', onResize)
        this._handlers.push({ target: globalThis, type: 'resize', handler: onResize })

        el.classList.remove('owl-loading')

        // owl-loaded   → display:block (owl.carousel.css: .owl-carousel { display:none })
        // owl-drag     → added by owl.carousel during event handler registration
        // owl-carousel-init + animated fadeIn → theme.js line 2124-2128
        el.classList.add('owl-loaded', 'owl-drag', 'owl-carousel-init', 'animated', 'fadeIn')
        setTimeout(() => el.classList.remove('animated', 'fadeIn'), 1000)

        el.style.height = 'auto'

        // Remove owl-carousel-loader sibling — theme.js lines 2141-2143
        const loader = el.previousElementSibling
        if (loader?.classList.contains('owl-carousel-loader')) loader.remove()

        // Reset owl-carousel-wrapper parent height — theme.js lines 2132-2138
        const wrapper = el.closest('.owl-carousel-wrapper')
        if (wrapper) setTimeout(() => { wrapper.style.height = '' }, 500)

        el.dispatchEvent(new CustomEvent('initialized.owl.carousel'))

        if (this.options.refresh) this._layout()
    }

    // ─── Clones ───────────────────────────────────────────────────────────────

    _buildClones() {
        const N     = this._originals.length
        const count = Math.min(N, Math.max(this._visibleCount, 1))

        this._clonesBefore = []
        for (let i = N - count; i < N; i++) {
            const c = this._originals[i].cloneNode(true)
            c.classList.add('cloned')
            this._clonesBefore.push(c)
        }
        this._clonesBefore.forEach(c => this._stage.insertBefore(c, this._originals[0]))

        this._clonesAfter = []
        for (let i = 0; i < count; i++) {
            const c = this._originals[i].cloneNode(true)
            c.classList.add('cloned')
            this._clonesAfter.push(c)
        }
        this._clonesAfter.forEach(c => this._stage.appendChild(c))

        this._cloneOffset = this._clonesBefore.length
        this._stagePos    = this._cloneOffset
    }

    _removeClones() {
        this._clonesBefore.forEach(c => c.remove())
        this._clonesAfter.forEach(c => c.remove())
        this._clonesBefore = []
        this._clonesAfter  = []
        this._cloneOffset  = 0
    }

    // ─── Layout ───────────────────────────────────────────────────────────────

    _getVisibleCount() {
        const w   = globalThis.innerWidth
        const bps = Object.keys(this.options.responsive).map(Number).sort((a, b) => a - b)
        let count = this.options.items ?? 1
        for (const bp of bps) {
            if (w >= bp) count = this.options.responsive[bp]?.items ?? count
        }
        return Math.max(1, count)
    }

    _layout(animate = false) {
        const allItems = Array.from(this._stage.querySelectorAll('.owl-item'))
        const outerW   = this._stageOuter.clientWidth || this._stageOuter.offsetWidth
        if (!outerW) return

        const pad = this.options.stagePadding
        const gap = this.options.margin
        const N   = this._visibleCount
        // With stagePadding: owl.carousel formula — items are narrowed for peek zones
        // Without stagePadding: items + gaps must fill outerW exactly (no next-item bleed)
        const itemW = pad > 0
            ? Math.floor((outerW - pad * 2) / N)
            : Math.floor((outerW - gap * (N - 1)) / N)

        // Matches owl.carousel line 378-380:
        //   width = last_coord + stagePadding*2 = N*(itemW+gap) + pad*2
        //   padding-left = padding-right = stagePadding
        this._stage.style.width        = `${allItems.length * (itemW + gap) + pad * 2}px`
        this._stage.style.paddingLeft  = pad ? `${pad}px` : ''
        this._stage.style.paddingRight = pad ? `${pad}px` : ''

        allItems.forEach(item => {
            item.style.width       = `${itemW}px`
            item.style.marginRight = gap ? `${gap}px` : ''
        })

        this._stagePos = (this.options.loop ? this._cloneOffset : 0) + this._currentIdx
        this._setTransform(this._translateX(this._stagePos), animate)
        this._updateActiveItems()
        this._updateDots()
    }

    _translateX(stagePos) {
        const outerW = this._stageOuter.clientWidth || this._stageOuter.offsetWidth
        const pad    = this.options.stagePadding
        const gap    = this.options.margin
        const N      = this._visibleCount
        const itemW  = pad > 0
            ? Math.floor((outerW - pad * 2) / N)
            : Math.floor((outerW - gap * (N - 1)) / N)
        // Matches owl.carousel: translate = -(stagePos * (itemW + gap))
        // No stagePadding offset — stage padding is on the stage element itself
        const x = stagePos * (itemW + gap)
        return this.options.rtl ? x : -x
    }

    // Matches owl.carousel: transition is set as '0.25s' (smartSpeed/1000)
    _setTransform(x, animate = true) {
        this._stage.style.transition = animate ? TRANSITION_S : ''
        this._stage.style.transform  = `translate3d(${x}px, 0px, 0px)`
    }

    // ─── Navigation ───────────────────────────────────────────────────────────

    _maxPos() {
        return Math.max(0, this._originals.length - this._visibleCount)
    }

    next(animate = true) {
        const N       = this._originals.length
        const prevIdx = this._currentIdx

        if (!this.options.loop) {
            if (this._stagePos >= this._maxPos()) return
            this._currentIdx = Math.min(this._currentIdx + 1, this._maxPos())
            this._stagePos   = this._currentIdx
        } else {
            this._currentIdx = (prevIdx + 1) % N
            this._stagePos++
        }

        this._dispatch('change.owl.carousel', { prevSlideIndex: prevIdx, nextSlideIndex: this._currentIdx })
        this._setTransform(this._translateX(this._stagePos), animate)
        this._updateActiveItems()
        this._updateDots()
        this._updateCenterItem()
        this._updateNavDisabled()

        if (this.options.loop) {
            const allCount         = this._stage.querySelectorAll('.owl-item').length
            const clonesAfterStart = allCount - this._clonesAfter.length
            if (this._stagePos >= clonesAfterStart) {
                setTimeout(() => {
                    this._stagePos = this._cloneOffset + this._currentIdx
                    this._setTransform(this._translateX(this._stagePos), false)
                }, SNAP_DELAY_MS)
            }
        }

        setTimeout(() => this._dispatch('changed.owl.carousel', { item: { index: this._currentIdx } }), TRANSITION_MS)
    }

    prev(animate = true) {
        const N       = this._originals.length
        const prevIdx = this._currentIdx

        if (!this.options.loop) {
            if (this._stagePos <= 0) return
            this._currentIdx = Math.max(this._currentIdx - 1, 0)
            this._stagePos   = this._currentIdx
        } else {
            this._currentIdx = (prevIdx - 1 + N) % N
            this._stagePos--
        }

        this._dispatch('change.owl.carousel', { prevSlideIndex: prevIdx, nextSlideIndex: this._currentIdx })
        this._setTransform(this._translateX(this._stagePos), animate)
        this._updateActiveItems()
        this._updateDots()
        this._updateCenterItem()
        this._updateNavDisabled()

        if (this.options.loop && this._stagePos < this._cloneOffset) {
            setTimeout(() => {
                this._stagePos = this._cloneOffset + this._currentIdx
                this._setTransform(this._translateX(this._stagePos), false)
            }, SNAP_DELAY_MS)
        }

        setTimeout(() => this._dispatch('changed.owl.carousel', { item: { index: this._currentIdx } }), TRANSITION_MS)
    }

    goTo(index, animate = true) {
        const N       = this._originals.length
        const prevIdx = this._currentIdx

        if (!this.options.loop) {
            this._currentIdx = Math.max(0, Math.min(index, this._maxPos()))
            this._stagePos   = this._currentIdx
        } else {
            this._currentIdx = ((index % N) + N) % N
            this._stagePos   = this._cloneOffset + this._currentIdx
        }

        this._setTransform(this._translateX(this._stagePos), animate)
        this._updateActiveItems()
        this._updateDots()
        this._updateCenterItem()
        this._updateNavDisabled()

        if (animate && prevIdx !== this._currentIdx) {
            this._dispatch('change.owl.carousel', { prevSlideIndex: prevIdx, nextSlideIndex: this._currentIdx })
            setTimeout(() => this._dispatch('changed.owl.carousel', { item: { index: this._currentIdx } }), TRANSITION_MS)
        }
    }

    // ─── State updates ────────────────────────────────────────────────────────

    _updateActiveItems() {
        const sp = this._stagePos
        Array.from(this._stage.querySelectorAll('.owl-item')).forEach((item, i) => {
            item.classList.toggle('active', i >= sp && i < sp + this._visibleCount)
        })
    }

    _updateDots() {
        if (!this._dots || this._dots.classList.contains('disabled')) return
        this._dots.querySelectorAll('.owl-dot').forEach((dot, i) => {
            dot.classList.toggle('active', i === this._currentIdx)
        })
    }

    // Matches owl.carousel nav plugin lines 3130-3131:
    // prev disabled when not loop AND at minimum; next disabled when at maximum
    _updateNavDisabled() {
        if (!this._prevBtn || !this._nextBtn) return
        if (this.options.loop) {
            this._prevBtn.classList.remove('disabled')
            this._nextBtn.classList.remove('disabled')
        } else {
            this._prevBtn.classList.toggle('disabled', this._stagePos <= 0)
            this._nextBtn.classList.toggle('disabled', this._stagePos >= this._maxPos())
        }
    }

    // carousel-center-active-item — theme.js lines 2196-2213
    _updateCenterItem() {
        if (!this.el.classList.contains('carousel-center-active-item')) return
        const active  = Array.from(this._stage.querySelectorAll('.owl-item.active'))
        const centerI = Math.floor((active.length - 1) / 2)
        this._stage.querySelectorAll('.owl-item').forEach(i => i.classList.remove('current'))
        active[centerI]?.classList.add('current')
    }

    // ─── Build nav / dots ─────────────────────────────────────────────────────

    // Always create .owl-nav — disabled class hides it via owl.carousel.css
    // when nav:false. Matches owl.carousel navigation plugin behavior.
    _buildNav() {
        const texts = Array.isArray(this.options.navText) && this.options.navText.length === 2
            ? this.options.navText
            : ['', '']  // '' → CSS ::before draws the FA chevron icon

        const nav  = document.createElement('div')
        nav.className = 'owl-nav'
        if (!this.options.nav) nav.classList.add('disabled')

        // role="presentation" — matches owl.carousel navElement: 'button type="button" role="presentation"'
        const prev = document.createElement('button')
        prev.type              = 'button'
        prev.className         = 'owl-prev'
        prev.setAttribute('role', 'presentation')
        prev.innerHTML         = texts[0]

        const next = document.createElement('button')
        next.type              = 'button'
        next.className         = 'owl-next'
        next.setAttribute('role', 'presentation')
        next.innerHTML         = texts[1]

        this._on(prev, 'click', e => { e.preventDefault(); this._stopAutoplay(); this.prev() })
        this._on(next, 'click', e => { e.preventDefault(); this._stopAutoplay(); this.next() })

        nav.append(prev, next)
        this.el.appendChild(nav)
        this._nav     = nav
        this._prevBtn = prev
        this._nextBtn = next
    }

    // Always create .owl-dots — disabled class hides it via owl.carousel.css
    // when dots:false. Matches owl.carousel dots plugin behavior.
    _buildDots() {
        const dots = document.createElement('div')
        dots.className = 'owl-dots'
        if (!this.options.dots) {
            dots.classList.add('disabled')
            this.el.appendChild(dots)
            this._dots = dots
            return
        }

        this._originals.forEach((_, i) => {
            const dot = document.createElement('button')
            dot.type      = 'button'
            dot.className = 'owl-dot'
            dot.innerHTML = '<span></span>'    // inner span required by owl.carousel.css dot styling
            this._on(dot, 'click', () => { this._stopAutoplay(); this.goTo(i) })
            dots.appendChild(dot)
        })

        this.el.appendChild(dots)
        this._dots = dots
        this._updateDots()
    }

    // navHorizontalOffset / navVerticalOffset / dotsHorizontalOffset / dotsVerticalOffset
    // Matches theme.js navigationOffsets()
    _applyNavOffsets() {
        const { navHorizontalOffset: nh, navVerticalOffset: nv,
                dotsHorizontalOffset: dh, dotsVerticalOffset: dv } = this.options

        if (this._nav) {
            if (nh && nv)  this._nav.style.transform = `translate3d(${nh}, ${nv}, 0)`
            else if (nh)   this._nav.style.transform = `translate3d(${nh}, 0, 0)`
            else if (nv)   this._nav.style.transform = `translate3d(0, ${nv}, 0)`
        }

        if (this._dots && !this._dots.classList.contains('disabled')) {
            if (dh && dv)  this._dots.style.transform = `translate3d(${dh}, ${dv}, 0)`
            else if (dh)   this._dots.style.transform = `translate3d(${dh}, 0, 0)`
            else if (dv)   this._dots.style.transform = `translate3d(0, ${dv}, 0)`
        }
    }

    // ─── Extras ───────────────────────────────────────────────────────────────

    // nav-outside — theme.js lines 2150-2173
    _handleNavOutside() {
        if (!this.el.classList.contains('nav-outside')) return
        const apply = () => {
            if (globalThis.innerWidth < 992) {
                this.options.stagePadding = 40
                this.el.classList.add('stage-margin')
            } else {
                this.options.stagePadding = 0
                this.el.classList.remove('stage-margin')
            }
            this._layout()
            this._applyNavOffsets()
        }
        globalThis.addEventListener('load',   apply)
        globalThis.addEventListener('resize', apply)
        this._handlers.push({ target: globalThis, type: 'load',   handler: apply })
        this._handlers.push({ target: globalThis, type: 'resize', handler: apply })
        apply()
    }

    // carousel-center-active-item — theme.js lines 2196-2213
    _handleCenterActiveItem() {
        if (!this.el.classList.contains('carousel-center-active-item')) return
        this._updateCenterItem()
        this._on(this.el, 'change.owl.carousel', () => {
            this._stage.querySelectorAll('.owl-item').forEach(i => i.classList.remove('current'))
        })
        this._on(this.el, 'changed.owl.carousel', () => setTimeout(() => this._updateCenterItem(), 100))
    }

    // nav-svg-arrows-1 — theme.js lines 2182-2186
    _handleSvgArrows() {
        if (!this.el.classList.contains('nav-svg-arrows-1') || !this._nav) return
        const prev = this._nav.querySelector('.owl-prev')
        const next = this._nav.querySelector('.owl-next')
        if (prev) prev.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-arrow-left"></i>')
        if (next) next.insertAdjacentHTML('beforeend', '<i class="fa-solid fa-arrow-right"></i>')
    }

    // ─── Autoplay ─────────────────────────────────────────────────────────────

    _startAutoplay() {
        clearInterval(this._autoPlayTimer)
        this._autoPlayTimer = setInterval(() => this.next(), this.options.autoplayTimeout)
    }

    _pauseAutoplay() { clearInterval(this._autoPlayTimer) }

    _stopAutoplay() {
        clearInterval(this._autoPlayTimer)
        this._autoPlayTimer = null
    }

    // ─── Swipe ────────────────────────────────────────────────────────────────

    _setupSwipe() {
        let startX = null
        this._on(this.el, 'touchstart', e => { startX = e.touches[0].clientX }, { passive: true })
        this._on(this.el, 'touchend', e => {
            if (startX === null) return
            const dx = e.changedTouches[0].clientX - startX
            if (Math.abs(dx) > 50) {
                this._stopAutoplay()
                if (this.options.rtl ? dx > 0 : dx < 0) this.next()
                else this.prev()
            }
            startX = null
        })
    }

    // ─── External navigation ──────────────────────────────────────────────────

    // data-carousel-navigate-id — matches theme.js PluginCarousel.carouselNavigate()
    _carouselNavigate() {
        const id = this.el.id
        if (!id) return
        document.querySelectorAll(`[data-carousel-navigate-id="#${id}"]`).forEach(btn => {
            const toIdx = parseInt(btn.dataset.carouselNavigateTo, 10) - 1
            this._on(btn, 'click', () => { this._stopAutoplay(); this.goTo(toIdx) })
        })
        this._on(this.el, 'change.owl.carousel', () => {
            document.querySelectorAll(`[data-carousel-navigate-id="#${id}"]`)
                .forEach(btn => btn.classList.remove('active'))
        })
        this._on(this.el, 'changed.owl.carousel', e => {
            const idx = e.detail?.item?.index
            if (idx != null) {
                document.querySelector(
                    `[data-carousel-navigate-id="#${id}"][data-carousel-navigate-to="${idx + 1}"]`
                )?.classList.add('active')
            }
        })
    }

    // ─── Resize ───────────────────────────────────────────────────────────────

    _onResize() {
        const newCount = this._getVisibleCount()
        if (newCount !== this._visibleCount) {
            this._visibleCount = newCount
            if (this.options.loop) {
                this._removeClones()
                this._buildClones()
            }
        }
        this._layout()
        this._applyNavOffsets()
        this._updateNavDisabled()
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────

    _dispatch(type, detail = {}) {
        this.el.dispatchEvent(new CustomEvent(type, { detail, bubbles: true }))
    }

    _on(target, type, handler, opts) {
        target.addEventListener(type, handler, opts)
        this._handlers.push({ target, type, handler })
    }

    // ─── Teardown ─────────────────────────────────────────────────────────────

    destroy() {
        this._stopAutoplay()
        clearTimeout(this._resizeTimer)
        this._handlers.forEach(({ target, type, handler }) => target.removeEventListener(type, handler))
        this._handlers = []

        if (this._stageOuter) {
            this._originals.forEach(item => {
                item.style.width       = ''
                item.style.marginRight = ''
                this.el.insertBefore(item, this._stageOuter)
            })
            this._stageOuter.remove()
        }
        this._nav?.remove()
        this._dots?.remove()
        this.el.classList.remove('owl-theme', 'owl-loaded', 'owl-drag', 'owl-carousel-init', 'animated', 'fadeIn')
        delete this.el.__carousel
    }
}
