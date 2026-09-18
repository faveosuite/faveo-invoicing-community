<script setup>
/**
 * reCAPTCHA v3 — scoreless, badge-only. No challenge UI and no rendered output:
 * Google shows its own floating badge (position is set at script-load time via
 * the provider). execute(action) silently returns a token the backend verifies
 * against the score threshold (and may then ask for a v2 fallback).
 */
import { useRecaptchaContext } from '../core/context.js'

const props = defineProps({
    action: { type: String, default: 'submit' },
})

const emit = defineEmits(['verify', 'error'])

const { config, grecaptcha } = useRecaptchaContext()

/**
 * @param {string} [action] override the default action.
 * @returns {Promise<string>} a fresh v3 token.
 */
function execute(action = props.action) {
    const api = grecaptcha()
    const siteKey = config.v3SiteKey
    if (!api || !siteKey) {
        return Promise.reject(new Error('reCAPTCHA v3 not ready.'))
    }
    return api
        .execute(siteKey, { action })
        .then(token => {
            emit('verify', token)
            return token
        })
        .catch(err => {
            emit('error', err)
            throw err
        })
}

defineExpose({ execute })
</script>

<template>
    <span class="g-recaptcha-v3" />
</template>
