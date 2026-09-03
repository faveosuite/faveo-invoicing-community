import { onBeforeUnmount, ref } from 'vue'

const SHOW_CLASS = 'show'

export function useNavFeatureToggle() {
    const open = ref(false)
    let outsideHandler

    function toggle(event) {
        event?.preventDefault()
        const toggleEl = event?.currentTarget
        const dropdown = toggleEl?.parentElement?.querySelector('.header-nav-features-dropdown')

        document.querySelectorAll(`.header-nav-features-dropdown.${SHOW_CLASS}`)
            .forEach(el => { if (el !== dropdown) el.classList.remove(SHOW_CLASS) })

        if (!dropdown) return

        const willOpen = !dropdown.classList.contains(SHOW_CLASS)
        dropdown.classList.toggle(SHOW_CLASS, willOpen)
        open.value = willOpen

        if (outsideHandler) {
            document.removeEventListener('click', outsideHandler)
            outsideHandler = null
        }

        if (willOpen) {
            const parent = toggleEl.parentElement
            outsideHandler = (e) => {
                if (parent && !parent.contains(e.target)) {
                    dropdown.classList.remove(SHOW_CLASS)
                    open.value = false
                    document.removeEventListener('click', outsideHandler)
                    outsideHandler = null
                }
            }
            setTimeout(() => document.addEventListener('click', outsideHandler), 0)
        }
    }

    onBeforeUnmount(() => {
        if (outsideHandler) document.removeEventListener('click', outsideHandler)
    })

    return { open, toggle }
}
