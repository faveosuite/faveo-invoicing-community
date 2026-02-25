/**
 * useBreadcrumb — derives page title and breadcrumb chain from the current route.
 *
 * Titles are translated via __() using meta.titleKey.
 * Falls back to meta.title (English string) if no translation is found.
 */
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

function translateTitle(meta) {
    if (!meta?.title) return 'Admin Panel'
    if (meta.titleKey) {
        const translated = __(meta.titleKey)
        if (translated !== meta.titleKey) return translated
    }
    return meta.title
}

export function useBreadcrumb() {
    const route  = useRoute()
    const router = useRouter()

    const pageTitle = computed(() => translateTitle(route.meta))

    const breadcrumbs = computed(() => {
        const crumbs   = []
        const segments = route.path.split('/').filter(Boolean)

        for (let i = 1; i <= segments.length; i++) {
            if (/^\d+$/.test(segments[i - 1])) continue

            const partialPath = '/' + segments.slice(0, i).join('/')
            const resolved    = router.resolve(partialPath)

            if (!resolved.matched.length || !resolved.meta?.title) continue

            const isLast = i === segments.length
            crumbs.push({
                title:    translateTitle(resolved.meta),
                to:       partialPath,
                isActive: isLast,
            })
        }

        return crumbs
    })

    return { pageTitle, breadcrumbs }
}
