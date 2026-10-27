import { inject, provide } from 'vue'
import { RECAPTCHA_CONTEXT } from './constants.js'

/**
 * The shape provided by <RecaptchaProvider> and consumed by the widget
 * components / useRecaptcha():
 *
 * {
 *   isReady:  Ref<boolean>          // grecaptcha loaded & ready
 *   error:    Ref<Error|null>       // load failure, if any
 *   config:   Reactive<{...}>       // resolved public config (mode, keys, theme...)
 *   grecaptcha: () => object|null   // accessor for window.grecaptcha (null until ready)
 * }
 */

export function provideRecaptcha(context) {
    provide(RECAPTCHA_CONTEXT, context)
    return context
}

export function useRecaptchaContext() {
    const context = inject(RECAPTCHA_CONTEXT, null)
    if (!context) {
        throw new Error(
            '[recaptcha] No provider found. Wrap the consuming component in <RecaptchaProvider>.'
        )
    }
    return context
}

/**
 * Soft variant: returns null instead of throwing. Useful for components that
 * should silently no-op when reCAPTCHA is not configured on the page.
 */
export function useRecaptchaContextOptional() {
    return inject(RECAPTCHA_CONTEXT, null)
}
