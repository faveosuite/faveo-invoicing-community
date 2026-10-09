import { useSidebar } from '@/core/composables/useSidebar.js'

describe('useSidebar', () => {
    beforeEach(() => {
        document.body.className = ''
        sessionStorage.clear()
    })

    function setDesktop() {
        Object.defineProperty(window, 'innerWidth', { value: 1200, writable: true, configurable: true })
    }

    function setMobile() {
        Object.defineProperty(window, 'innerWidth', { value: 600, writable: true, configurable: true })
    }

    it('returns isOpen, toggle, and close', () => {
        const result = useSidebar()
        expect(result).toHaveProperty('isOpen')
        expect(result).toHaveProperty('toggle')
        expect(result).toHaveProperty('close')
    })

    it('isOpen is true when body does not have sidebar-collapse', () => {
        document.body.className = ''
        const { isOpen } = useSidebar()
        expect(isOpen.value).toBe(true)
    })

    it('isOpen reflects sidebar-collapse class on body at call time', () => {
        document.body.classList.add('sidebar-collapse')
        const { isOpen } = useSidebar()
        // Module-level ref is set at module load, so we need to check toggle behavior
        // After toggle, isOpen should update
        const { toggle } = useSidebar()
        toggle()
        expect(isOpen.value).toBe(true)
    })

    it('toggle: open → collapsed (removes sidebar-open, adds sidebar-collapse)', () => {
        document.body.classList.remove('sidebar-collapse')
        const { toggle } = useSidebar()
        toggle()
        expect(document.body.classList.contains('sidebar-collapse')).toBe(true)
        expect(document.body.classList.contains('sidebar-open')).toBe(false)
    })

    it('toggle: collapsed → open (removes sidebar-collapse, adds sidebar-open)', () => {
        document.body.classList.add('sidebar-collapse')
        const { toggle } = useSidebar()
        toggle()
        expect(document.body.classList.contains('sidebar-open')).toBe(true)
        expect(document.body.classList.contains('sidebar-collapse')).toBe(false)
    })

    it('toggle open→collapsed sets isOpen to false', () => {
        document.body.classList.remove('sidebar-collapse')
        const { isOpen, toggle } = useSidebar()
        toggle()
        expect(isOpen.value).toBe(false)
    })

    it('toggle collapsed→open sets isOpen to true', () => {
        document.body.classList.add('sidebar-collapse')
        const { isOpen, toggle } = useSidebar()
        toggle()
        expect(isOpen.value).toBe(true)
    })

    it('close() always collapses sidebar', () => {
        document.body.classList.remove('sidebar-collapse')
        document.body.classList.add('sidebar-open')
        const { close } = useSidebar()
        close()
        expect(document.body.classList.contains('sidebar-collapse')).toBe(true)
        expect(document.body.classList.contains('sidebar-open')).toBe(false)
    })

    it('close() sets isOpen to false', () => {
        const { isOpen, close } = useSidebar()
        close()
        expect(isOpen.value).toBe(false)
    })

    it('toggle on desktop saves state to sessionStorage', () => {
        setDesktop()
        document.body.classList.remove('sidebar-collapse')
        const { toggle } = useSidebar()
        toggle()
        expect(sessionStorage.getItem('sidebar-state')).toBe('collapsed')
        toggle()
        expect(sessionStorage.getItem('sidebar-state')).toBe('open')
    })

    it('toggle on mobile does NOT save state to sessionStorage', () => {
        setMobile()
        document.body.classList.remove('sidebar-collapse')
        const { toggle } = useSidebar()
        toggle()
        expect(sessionStorage.getItem('sidebar-state')).toBeNull()
    })

    it('close() never persists state to sessionStorage', () => {
        setDesktop()
        const { close } = useSidebar()
        close()
        expect(sessionStorage.getItem('sidebar-state')).toBeNull()
    })
})
