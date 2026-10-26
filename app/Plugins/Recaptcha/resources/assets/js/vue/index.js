/**
 * Faveo in-house Vue reCAPTCHA.
 *
 * A self-contained layer over Google's grecaptcha global — no external Vue
 * reCAPTCHA dependency. Drives v2 checkbox, v2 invisible and v3, config-driven
 * from the backend, with disabled-mode and v3 -> v2 fallback support.
 *
 * Import via the `@recaptcha` alias, e.g.:
 *   import { RecaptchaProvider, RecaptchaField } from '@recaptcha'
 */

// Components
export { default as RecaptchaProvider } from './components/RecaptchaProvider.vue'
export { default as RecaptchaField } from './components/RecaptchaField.vue'
export { default as RecaptchaCheckbox } from './components/RecaptchaCheckbox.vue'
export { default as RecaptchaV2Invisible } from './components/RecaptchaV2Invisible.vue'
export { default as RecaptchaV3 } from './components/RecaptchaV3.vue'

// Composables
export { useRecaptchaConfig, fetchRecaptchaConfig } from './composables/useRecaptchaConfig.js'

// Context helpers (for advanced/custom consumers)
export {
    useRecaptchaContext,
    useRecaptchaContextOptional,
    provideRecaptcha,
} from './core/context.js'

// Low-level loader + constants
export { loadRecaptcha } from './core/loader.js'
export { MODE, FAILOVER, VERSION_TO_MODE, versionToMode } from './core/constants.js'
