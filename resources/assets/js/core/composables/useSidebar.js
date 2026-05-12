/**
 * useSidebar — mirrors AdminLTE 4's PushMenu toggle logic.
 *
 * AdminLTE uses body classes to control sidebar state:
 *   sidebar-mini      →  enables mini sidebar mode (icons only when collapsed)
 *   sidebar-collapse  →  sidebar is collapsed (mini icons if sidebar-mini is set)
 *   sidebar-open      →  sidebar is fully visible
 *
 * We replicate the same class manipulation so all AdminLTE CSS transitions
 * continue to work without relying on AdminLTE JS event delegation.
 *
 * Desktop toggle state is persisted in sessionStorage so it survives page
 * refreshes within the same tab.  Mobile state is never saved — the sidebar
 * always starts closed on mobile.
 */
import { ref } from 'vue'

const STORAGE_KEY  = 'sidebar-state'
const isDesktop    = () => window.innerWidth >= 992   // matches sidebar-expand-lg

// ── Restore persisted desktop state before first render ───────────────────────
// Runs once at module load (before Vue mounts) so there is no visible flash.
if (isDesktop()) {
    const saved = sessionStorage.getItem(STORAGE_KEY)
    if (saved === 'collapsed') {
        document.body.classList.add('sidebar-collapse')
        document.body.classList.remove('sidebar-open')
    } else if (saved === 'open') {
        document.body.classList.remove('sidebar-collapse')
        document.body.classList.add('sidebar-open')
    }
    // No saved value → leave blade default as-is
}

// Module-level so Navbar and DefaultLayout share the same reactive state
const isOpen = ref(!document.body.classList.contains('sidebar-collapse'))

export function useSidebar() {
    function toggle() {
        if (document.body.classList.contains('sidebar-collapse')) {
            document.body.classList.remove('sidebar-collapse')
            document.body.classList.add('sidebar-open')
            isOpen.value = true
            if (isDesktop()) sessionStorage.setItem(STORAGE_KEY, 'open')
        } else {
            document.body.classList.remove('sidebar-open')
            document.body.classList.add('sidebar-collapse')
            isOpen.value = false
            if (isDesktop()) sessionStorage.setItem(STORAGE_KEY, 'collapsed')
        }
    }

    // Called only from the mobile overlay tap — never persisted
    function close() {
        document.body.classList.remove('sidebar-open')
        document.body.classList.add('sidebar-collapse')
        isOpen.value = false
    }

    return { isOpen, toggle, close }
}
