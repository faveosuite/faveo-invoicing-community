/**
 * useBreadcrumb — derives page title and breadcrumb chain from the current route.
 *
 * Strategy: walk the path segments from left to right, calling router.resolve()
 * on each accumulated sub-path.  If a segment resolves to a named route that
 * carries a meta.title, add it to the chain.  Pure numeric segments (dynamic IDs
 * like /users/5) are skipped — they won't match a static route anyway.
 *
 * Examples
 *   /dashboard                   → Home › Dashboard
 *   /users/create                → Home › Users › New User
 *   /users/5/edit                → Home › Users › Edit User
 *   /products/coupons/3/edit     → Home › Products › Coupons › Edit Coupon
 *   /settings/email/settings     → Home › Email Settings
 */
import { computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'

export function useBreadcrumb() {
    const route  = useRoute()
    const router = useRouter()

    const pageTitle = computed(() => route.meta?.title ?? 'Admin Panel')

    const breadcrumbs = computed(() => {
        const crumbs   = []
        const segments = route.path.split('/').filter(Boolean)

        for (let i = 1; i <= segments.length; i++) {
            // Skip pure numeric segments — they are dynamic IDs, not named sections
            if (/^\d+$/.test(segments[i - 1])) continue

            const partialPath = '/' + segments.slice(0, i).join('/')
            const resolved    = router.resolve(partialPath)

            if (!resolved.matched.length || !resolved.meta?.title) continue

            const isLast = i === segments.length
            crumbs.push({
                title:    resolved.meta.title,
                to:       partialPath,
                isActive: isLast,
            })
        }

        return crumbs
    })

    return { pageTitle, breadcrumbs }
}
