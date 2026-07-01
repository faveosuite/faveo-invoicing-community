<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <input
            ref="phoneRef"
            type="tel"
            :class="['form-control', { 'is-invalid': fieldError }]"
            :value="value"
            @input="onInput"
            @keypress="numbersOnly"
        />
        <div v-if="fieldError" class="invalid-feedback d-block">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils'

const props = defineProps({
    name:           { type: String, required: true },
    label:          { type: String, default: '' },
    value:          { type: [String, Number], default: '' },
    required:       { type: Boolean, default: false },
    onChange:       { type: Function, default: () => {} },
    initialCountry: { type: String, default: 'auto' },
    error:          { type: String, default: undefined },
})

const emit = defineEmits(['countryChange'])

const fieldError = computed(() => props.error ?? '')

const phoneRef = ref(null)
let iti = null

function onInput(e) {
    props.onChange(e.target.value, props.name)
}

function numbersOnly(e) {
    const code = e.which ?? e.keyCode
    if (code > 31 && (code < 48 || code > 57)) e.preventDefault()
}

function emitCountry() {
    if (!iti) return
    const data = iti.getSelectedCountryData()
    if (!data.iso2) return
    emit('countryChange', { iso: data.iso2.toUpperCase(), dialCode: data.dialCode })
}

onMounted(() => {
    if (!phoneRef.value) return
    const options = {
        initialCountry:  props.initialCountry || 'auto',
        separateDialCode: true,
        allowDropdown:    true,
        showFlags:        true,
        formatAsYouType:  false,
        strictMode:       true,
        formatOnDisplay:  false,
        nationalMode:     false,
        excludeCountries: ['ax'],
    }
    if (options.initialCountry === 'auto') {
        options.geoIpLookup = (success) => {
            fetch('https://ipapi.co/json')
                .then(r => r.json())
                .then(d => success(d.country_code))
                .catch(() => success('IN'))
        }
    }
    iti = intlTelInput(phoneRef.value, options)
    phoneRef.value.addEventListener('countrychange', emitCountry)
    emitCountry()
})

onBeforeUnmount(() => {
    iti?.destroy()
    iti = null
})
</script>

<style>
@import 'intl-tel-input/build/css/intlTelInput.css';

.iti--allow-dropdown { width: 100% !important; }

.iti__selected-country {
    padding: 5px !important;
    background-color: #f2f2f2 !important;
    outline: none !important;
}

.iti__selected-country-primary:hover { background-color: #f2f2f2 !important; }

.iti--inline-dropdown .iti__dropdown-content {
    max-width: 355px !important;
    min-width: 270px !important;
}

.iti .iti__selected-dial-code { margin-right: 4px; }

.iti__search-input { padding-top: 10px !important; padding-bottom: 8px !important; }

.iti__arrow { margin-right: 5px !important; }
</style>
