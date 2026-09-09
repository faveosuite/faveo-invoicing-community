/**
 * useBreadcrumb — derives page title and breadcrumb chain from the current route.
 *
 * Titles are translated via __() using meta.titleKey.
 * Falls back to meta.title (English string) if no translation is found.
 */
import { computed, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

// Module-level ref so any component can override the page header title
const _titleOverride = ref(null)

export function setPageTitle(title) {
    _titleOverride.value = title || null
}

function translateTitle(meta) {
    if (!meta?.title) {
        return ''
    }
    if (meta.titleKey) {
        const translated = __(meta.titleKey)
        if (translated !== meta.titleKey) return translated
    }
    return meta.title
}

export function useBreadcrumb() {
    const route  = useRoute()
    const router = useRouter()

    const pageTitle = computed(() => _titleOverride.value || translateTitle(route.meta))

    const breadcrumbs = computed(() => {
        // Explicit override via meta.breadcrumb: array of { titleKey?, title, to? },
        // or a function(route) returning that array — used for dynamic parent
        // crumbs on routes with an :id segment (e.g. linking back to the specific
        // record's own page, which path-segment derivation can't express).
        // Used where path-segment derivation would be wrong (e.g. nested auth routes).
        const override = typeof route.meta?.breadcrumb === 'function'
            ? route.meta.breadcrumb(route)
            : route.meta?.breadcrumb

        if (Array.isArray(override)) {
            return override.map((c, i, arr) => ({
                title: translateTitle(c),
                to: c.to ?? route.path,
                isActive: i === arr.length - 1,
            }))
        }

        const crumbs   = []
        const segments = route.path.split('/').filter(Boolean)

        for (let i = 1; i <= segments.length; i++) {
            if (/^\d+$/.test(segments[i - 1]) && i !== segments.length) continue

            const partialPath = '/' + segments.slice(0, i).join('/')
            const resolved    = router.resolve(partialPath)

            // The catch-all route (adminRouter.js's `/:pathMatch(.*)*`) matches
            // any partial path that isn't a real route — e.g. `/products/8/versions`
            // has no index page of its own, only `.../create` and `.../:id/edit`
            // do. Without this check, that partial match's "Not Found" title
            // would get inserted as a spurious crumb.
            if (!resolved.matched.length || !resolved.meta?.title || resolved.meta?.isErrorPage) continue

            const isLast = i === segments.length
            const title  = translateTitle(resolved.meta)

            if (crumbs.length && crumbs[crumbs.length - 1].title === title) {
                // Same title as previous crumb — if this is the last segment promote
                // the existing crumb to active so it renders as a disabled span
                if (isLast) crumbs[crumbs.length - 1].isActive = true
                continue
            }

            crumbs.push({ title, to: partialPath, isActive: isLast })
        }

        return crumbs
    })

    return { pageTitle, breadcrumbs }
}
