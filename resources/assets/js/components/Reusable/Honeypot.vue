<template>
    <!-- Visually hidden + off-screen + aria-hidden so humans never see/focus it,
         but DOM-filling bots will populate the pot input → backend rejects. -->
    <div class="honeypot-field" aria-hidden="true">
        <label :for="pot">{{ label }}</label>
        <input
            ref="potRef"
            type="text"
            tabindex="-1"
            autocomplete="off"
            :name="`${name}[${pot}]`"
            :id="pot"
            v-model="potValue"
        />
    </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import http from '@/plugins/axios'

const props = defineProps({
    // The form field name the backend App\Rules\Honeypot is attached to
    // (e.g. 'login', 'registerForm', 'forgot', 'reset').
    name:       { type: String, required: true },
    modelValue: { type: [Object, String], default: () => ({}) },
    label:      { type: String, default: 'Do not fill this field' },
})

const emit = defineEmits(['update:modelValue', 'ready'])

const pot      = ref('')   // pot sub-field name (starts with 'p')
const time     = ref('')   // time sub-field name (starts with 't')
const token    = ref('')   // server-encrypted timestamp
const potValue = ref('')   // empty for humans; bots fill it
const ready    = ref(false)

const MAX_ATTEMPTS = 3

// Emit the { pXxx: <pot>, tYyy: <token> } array the backend rule expects.
function emitValue() {
    if (!pot.value || !time.value || !token.value) return
    emit('update:modelValue', { [pot.value]: potValue.value, [time.value]: token.value })
}

function setReady(value) {
    ready.value = value
    emit('ready', value)
}

watch(potValue, emitValue)

// Fetch the encrypted-time token, retrying transient failures so a single blip
// doesn't permanently brick the form for the rest of the page's life.
async function load(attempt = 1) {
    try {
        const { data } = await http.get('honeypot')
        const d = data?.data ?? {}
        pot.value   = d.pot   ?? ''
        time.value  = d.time  ?? ''
        token.value = d.token ?? ''
        emitValue()
        setReady(Boolean(token.value))
    } catch {
        if (attempt < MAX_ATTEMPTS) {
            setTimeout(() => load(attempt + 1), 400 * attempt)
        } else {
            setReady(false)
        }
    }
}

onMounted(() => load())

// Allow the parent to refresh the token (e.g. after a failed submit) and re-emit.
defineExpose({ reload: () => load() })
</script>

<style scoped>
.honeypot-field {
    position: absolute !important;
    left: -9999px !important;
    top: -9999px !important;
    width: 1px;
    height: 1px;
    overflow: hidden;
    opacity: 0;
}
</style>
