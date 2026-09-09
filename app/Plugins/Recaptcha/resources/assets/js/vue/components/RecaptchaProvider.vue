<script setup>
/**
 * <RecaptchaProvider> — loads the grecaptcha script and provides a reactive
 * context to every reCAPTCHA widget/consumer beneath it.
 *
 * Usage:
 *   <RecaptchaProvider>
 *     <RecaptchaField ref="captcha" action="login" page-id="login" />
 *   </RecaptchaProvider>
 *
 * By default it pulls the public config from the backend. Callers may override
 * any field via props (the admin settings preview does this to live-preview an
 * unsaved key).
 */
import { computed, reactive, ref, watch } from 'vue'
import { loadRecaptcha } from '../core/loader.js'
import { provideRecaptcha } from '../core/context.js'
import { MODE } from '../core/constants.js'
import { useRecaptchaConfig } from '../composables/useRecaptchaConfig.js'

const props = defineProps({
    // When false, skip the backend fetch and use the explicit props below.
    autoConfig: { type: Boolean, default: true },
    mode: { type: String, default: null },
    v2SiteKey: { type: String, default: null },
    v3SiteKey: { type: String, default: null },
    theme: { type: String, default: null },
    size: { type: String, default: null },
    badge: { type: String, default: null },
    lang: { type: String, default: null },
    enabled: { type: Boolean, default: null },
})

const { config: fetched } = props.autoConfig
    ? useRecaptchaConfig()
    : { config: reactive({}) }

// Merge: explicit props win over fetched config.
const config = reactive({
    get enabled() { return props.enabled ?? fetched.enabled ?? false },
    get mode() { return props.mode ?? fetched.mode ?? MODE.V3 },
    get v2SiteKey() { return props.v2SiteKey ?? fetched.v2SiteKey ?? '' },
    get v3SiteKey() { return props.v3SiteKey ?? fetched.v3SiteKey ?? '' },
    get failoverAction() { return fetched.failoverAction ?? 'none' },
    get theme() { return props.theme ?? fetched.theme ?? 'light' },
    get size() { return props.size ?? fetched.size ?? 'normal' },
    get badge() { return props.badge ?? fetched.badge ?? 'bottomright' },
    get lang() { return props.lang ?? fetched.lang ?? 'en' },
})

const isReady = ref(false)
const error = ref(null)
let grecaptchaRef = null

// `render` param: v3 needs the site key (for the badge); everything else is explicit.
const renderParam = computed(() =>
    config.mode === MODE.V3 && config.v3SiteKey ? config.v3SiteKey : 'explicit'
)

function bootstrap() {
    if (!config.enabled) {
        // Disabled: provide a ready-but-inert context so consumers no-op cleanly.
        isReady.value = false
        return
    }
    if (!config.v2SiteKey && !config.v3SiteKey) {
        isReady.value = false
        return
    }

    isReady.value = false
    loadRecaptcha({ render: renderParam.value, hl: config.lang, badge: config.badge })
        .then(grecaptcha => {
            grecaptchaRef = grecaptcha
            isReady.value = true
            error.value = null
        })
        .catch(err => {
            error.value = err
            isReady.value = false
        })
}

// Bootstrap on mount and re-bootstrap when the relevant config inputs change.
watch(
    () => [config.enabled, config.mode, config.v2SiteKey, config.v3SiteKey, config.lang],
    bootstrap,
    { immediate: true }
)

const context = provideRecaptcha({
    isReady,
    error,
    config,
    grecaptcha: () => grecaptchaRef,
})

defineExpose({ context, isReady, error })
</script>

<template>
    <slot :ready="isReady" :error="error" :config="config" />
</template>
