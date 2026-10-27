/**
 * Converts a Vue Router path pattern ('/orders/:id/renew') into the lookup
 * key format used by the server-shipped route SEO maps ('orders/*\/renew')
 * — strips the leading slash and replaces each :param segment with '*'.
 */
export function normalizeRoutePattern(rawPath) {
    return rawPath.replace(/^\//, '').replace(/:[^/]+/g, '*')
}
