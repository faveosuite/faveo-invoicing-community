<script setup>
/**
 * reCAPTCHA v2 invisible.
 *
 * No visible checkbox — a challenge is presented (if Google decides one is
 * needed) when execute() is called. execute() resolves with the token, or
 * rejects if the user closes/expires the challenge.
 */
import { onBeforeUnmount, ref, watch } from 'vue'
import { useRecaptchaContext } from '../core/context.js'

const props = defineProps({
    badge: { type: String, default: null }, // bottomright | bottomleft | inline
    tabindex: { type: Number, default: 0 },
})

const emit = defineEmits(['verify', 'expired', 'error', 'render'])

const { isReady, config, grecaptcha } = useRecaptchaContext()

const root = ref(null)
let widgetId = null
let pending = null // { resolve, reject }

function settleResolve(token) {
    if (pending) { pending.resolve(token); pending = null }
}
function settleReject(err) {
    if (pending) { pending.reject(err); pending = null }
}

function render() {
    const api = grecaptcha()
    if (!api || !root.value || widgetId !== null) {
        return
    }
    const siteKey = config.v2SiteKey
    if (!siteKey) {
        return
    }

    widgetId = api.render(root.value, {
        sitekey: siteKey,
        size: 'invisible',
        badge: props.badge ?? config.badge,
        tabindex: props.tabindex,
        callback: token => {
            emit('verify', token)
            settleResolve(token)
        },
        'expired-callback': () => {
            emit('expired')
            settleReject(new Error('reCAPTCHA challenge expired. Please try again.'))
        },
        'error-callback': () => {
            const err = new Error('reCAPTCHA challenge failed or was dismissed.')
            emit('error', err)
            settleReject(err)
        },
    })
    emit('render', widgetId)
}

/**
 * Programmatically run the challenge and resolve with a fresh token.
 * @returns {Promise<string>}
 */
function execute() {
    const api = grecaptcha()
    if (!api || widgetId === null) {
        return Promise.reject(new Error('reCAPTCHA v2 invisible not ready.'))
    }
    return new Promise((resolve, reject) => {
        pending = { resolve, reject }
        try {
            api.execute(widgetId)
        } catch (err) {
            pending = null
            reject(err)
        }
    })
}

function reset() {
    const api = grecaptcha()
    if (api && widgetId !== null) {
        api.reset(widgetId)
    }
    settleReject(new Error('reCAPTCHA reset.'))
}

watch(isReady, ready => { if (ready) render() }, { immediate: true })

onBeforeUnmount(reset)

defineExpose({ execute, reset, widgetId: () => widgetId })
</script>

<template>
    <div ref="root" class="g-recaptcha-invisible"></div>
</template>
