<script setup>
/**
 * reCAPTCHA v2 "I'm not a robot" checkbox.
 *
 * Renders an explicit widget into its own container and surfaces the lifecycle
 * via events plus an imperative API (getResponse/reset/execute is N/A here).
 */
import { onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { useRecaptchaContext } from '../core/context.js'

const props = defineProps({
    theme: { type: String, default: null }, // light | dark
    size: { type: String, default: null },  // normal | compact
    tabindex: { type: Number, default: 0 },
})

const emit = defineEmits(['verify', 'expired', 'error', 'render'])

const { isReady, config, grecaptcha } = useRecaptchaContext()

const root = ref(null)
const response = ref('')
let widgetId = null

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
        theme: props.theme ?? config.theme,
        size: props.size ?? config.size,
        tabindex: props.tabindex,
        callback: token => {
            response.value = token
            emit('verify', token)
        },
        'expired-callback': () => {
            response.value = ''
            emit('expired')
        },
        'error-callback': () => {
            response.value = ''
            emit('error', new Error('reCAPTCHA v2 checkbox error.'))
        },
    })
    emit('render', widgetId)
}

function reset() {
    const api = grecaptcha()
    if (api && widgetId !== null) {
        api.reset(widgetId)
    }
    response.value = ''
}

function getResponse() {
    return response.value
}

watch(isReady, ready => { if (ready) render() })
onMounted(() => { if (isReady.value) render() })

onBeforeUnmount(() => {
    // grecaptcha has no destroy(); resetting frees the challenge state.
    reset()
})

defineExpose({ reset, getResponse, response, widgetId: () => widgetId })
</script>

<template>
    <div ref="root" class="g-recaptcha-checkbox"></div>
</template>
