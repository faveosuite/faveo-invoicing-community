<script setup>
/**
 * <RecaptchaField> — the drop-in any form uses. It hides every v2/v3 difference
 * behind one imperative API and owns the v3 -> v2-checkbox fallback transition.
 *
 *   <RecaptchaProvider>
 *     <RecaptchaField ref="captcha" action="login" page-id="login" />
 *   </RecaptchaProvider>
 *
 *   // on submit:
 *   const payload = await captcha.value.getPayload()
 *   // payload => { 'g-recaptcha-response': token|null, page_id }
 *   ...send with the form...
 *   // on 422 { show_v2_recaptcha: true }:
 *   captcha.value.triggerFallback()
 *
 * When reCAPTCHA is disabled, getToken()/getPayload() resolve with a null token
 * so the caller can submit unchanged.
 */
import { computed, ref } from 'vue'
import { useRecaptchaContext } from '../core/context.js'
import { MODE, FAILOVER } from '../core/constants.js'
import RecaptchaCheckbox from './RecaptchaCheckbox.vue'
import RecaptchaV2Invisible from './RecaptchaV2Invisible.vue'
import RecaptchaV3 from './RecaptchaV3.vue'

const props = defineProps({
    action: { type: String, default: 'submit' },
    pageId: { type: String, required: true },
    // Show the built-in inline validation message on a missing token.
    showError: { type: Boolean, default: true },
    errorMessage: { type: String, default: 'Please complete the reCAPTCHA.' },
    badge: { type: String, default: null },
})

const emit = defineEmits(['fallback', 'verify', 'error'])

const { config } = useRecaptchaContext()

const checkboxRef = ref(null)
const invisibleRef = ref(null)
const v3Ref = ref(null)

const fallbackActive = ref(false)
const error = ref('')

const disabled = computed(() => !config.enabled)

// Effective mode after accounting for an active fallback.
const effectiveMode = computed(() => {
    if (disabled.value) return null
    if (fallbackActive.value) return MODE.V2_CHECKBOX
    return config.mode
})

const showCheckbox = computed(() => effectiveMode.value === MODE.V2_CHECKBOX)
const showInvisible = computed(() => effectiveMode.value === MODE.V2_INVISIBLE)
const showV3 = computed(() => effectiveMode.value === MODE.V3)

function clearError() {
    error.value = ''
}

function fail() {
    if (props.showError) {
        error.value = props.errorMessage
    }
}

/**
 * Resolve a token for the current mode. Returns null when disabled or when no
 * token could be obtained (after surfacing the inline error).
 * @returns {Promise<string|null>}
 */
async function getToken(actionOverride) {
    clearError()
    if (disabled.value) {
        return null
    }

    try {
        let token = null
        if (showCheckbox.value) {
            token = checkboxRef.value?.getResponse() || null
            if (!token) { fail(); return null }
        } else if (showInvisible.value) {
            token = await invisibleRef.value?.execute()
        } else if (showV3.value) {
            token = await v3Ref.value?.execute(actionOverride ?? props.action)
        }

        if (!token) { fail(); return null }
        emit('verify', token)
        return token
    } catch (err) {
        emit('error', err)
        fail()
        return null
    }
}

/**
 * Convenience: the exact request fields the backend middleware expects.
 * @returns {Promise<{'g-recaptcha-response': string|null, page_id: string}>}
 */
async function getPayload(actionOverride) {
    const token = await getToken(actionOverride)
    return { 'g-recaptcha-response': token, page_id: props.pageId }
}

/**
 * Switch this field to the v2 checkbox after the backend asks for a fallback
 * (low v3 score + failover_action = v2_checkbox). Idempotent.
 * @returns {boolean} whether the fallback was activated.
 */
function triggerFallback() {
    if (disabled.value || fallbackActive.value) {
        return fallbackActive.value
    }
    if (config.failoverAction !== FAILOVER.V2_CHECKBOX || !config.v2SiteKey) {
        return false
    }
    fallbackActive.value = true
    clearError()
    emit('fallback')
    return true
}

function reset() {
    clearError()
    checkboxRef.value?.reset?.()
    invisibleRef.value?.reset?.()
}

defineExpose({
    getToken,
    getPayload,
    triggerFallback,
    reset,
    disabled,
    inFallback: computed(() => fallbackActive.value),
    mode: effectiveMode,
})
</script>

<template>
    <div v-if="!disabled" class="recaptcha-field">
        <RecaptchaCheckbox
            v-if="showCheckbox"
            ref="checkboxRef"
            @verify="clearError"
            @error="emit('error', $event)"
        />
        <RecaptchaV2Invisible
            v-else-if="showInvisible"
            ref="invisibleRef"
            :badge="badge"
            @error="emit('error', $event)"
        />
        <RecaptchaV3
            v-else-if="showV3"
            ref="v3Ref"
            :action="action"
        />

        <div v-if="error" class="invalid-feedback d-block recaptcha-field__error">
            {{ error }}
        </div>
    </div>
</template>
