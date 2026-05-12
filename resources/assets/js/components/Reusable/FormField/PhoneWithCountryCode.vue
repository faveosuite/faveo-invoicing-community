<template>
    <FormFieldTemplate :classname="classname" :label="lang(labelName)" :name="name">
        <div class="intl-tel-input allow-dropdown separate-dial-code iti-sdc-3 telephone-input">
            <input class="form-control telephone-input-field" ref="phoneInput"
                   :class="{ 'has-error': showError }"
                   type="tel" v-model="changedValue"
                   @input="onChange(changedValue, name)"
                   @keypress="isNumber"
                   :style="inputStyle" id="id" />
            <div v-if="showError" class="error-block is-danger">{{ lang(errorMsg) }}</div>
        </div>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue'
import intlTelInput from 'intl-tel-input/intlTelInputWithUtils'
import { lang } from '@/helpers/extraLogics'
import { findObjectByKey } from '@/helpers/extraLogics'
import axios from '@/plugins/axios'
import FormFieldTemplate from './FormFieldTemplate.vue'

const props = defineProps({
    labelName:   { type: String,               default: 'phone_number' },
    classname:   { type: String,               default: '' },
    value:       { type: [String, Number, null], required: true },
    name:        { type: String,               required: true },
    onChange:    { type: Function,             required: true },
    countryIso:  { type: String,               default: '' },
    countryCode: { type: [Number, String, null], required: true },
    inputStyle:  { type: Object,               default: () => ({}) },
    fieldType:   { type: String,               default: 'FIXED_LINE_OR_MOBILE' },
})

const emit = defineEmits(['validPhoneNumber', 'countCode', 'countIso'])

const phoneInput = ref(null)
const changedValue = ref(props.value)
const showError = ref(false)
const errorMsg = ref('')
const showField = ref(false)
const isMounted = ref(false)

let iti = null

onMounted(() => {
    const input = phoneInput.value
    if (!input) return

    iti = intlTelInput(input, {
        initialCountry: props.countryIso || (props.countryCode ? getIsoByCountryCode(props.countryCode) : 'auto'),
        geoIpLookup: fetchGeoIpCountryCode,
        validationNumberType: props.fieldType,
        placeholderNumberType: props.fieldType,
        allowDropdown: true,
        separateDialCode: true,
        i18n: 'en',
        showFlags: true,
        formatAsYouType: false,
        strictMode: true,
        formatOnDisplay: false,
        nationalMode: false,
        excludeCountries: ['ax'],
        customPlaceholder: (placeholder) => placeholder,
    })

    input.addEventListener('countrychange', updatePhone)

    if (iti.options.initialCountry === 'auto') {
        emitIsoAndDialCodes()
    }

    isMounted.value = true
})

onBeforeUnmount(() => {
    iti?.destroy()
    iti = null
    showField.value = false
})

watch(() => props.value, (newVal) => {
    changedValue.value = newVal
    if (!isMounted.value) return
    const isValid = newVal === '' || iti?.isValidNumber()
    emit('validPhoneNumber', props.name, isValid)
    showError.value = !isValid
    errorMsg.value = isValid ? '' : 'invalid_phone_number'
})

function updatePhone() {
    if (showField.value) changedValue.value = ''
    showField.value = true
    emitIsoAndDialCodes()
}

function isNumber(evt) {
    const charCode = evt.which ?? evt.keyCode
    if (charCode > 31 && (charCode < 48 || charCode > 57)) evt.preventDefault()
}

function fetchGeoIpCountryCode(success = () => {}) {
    axios.get('https://ipapi.co/json')
        .then(res => success(res.data.country_code))
        .catch(() => success('IN'))
}

function getIsoByCountryCode(code) {
    const countryData = intlTelInput.getCountryData()
    const country = findObjectByKey(countryData, 'dialCode', code)
    return country ? country.iso2 : 'auto'
}

function emitIsoAndDialCodes() {
    const selected = iti?.getSelectedCountryData()
    if (selected) {
        emit('countCode', selected.dialCode)
        emit('countIso', selected.iso2)
    }
}
</script>

<style>
@import 'intl-tel-input/build/css/intlTelInput.css';
.iti--allow-dropdown { width: 100% !important; }
.telephone-input { width: 100%; }
.iti__selected-country { padding: 5px !important; background-color: #F2F2F2 !important; outline: none !important; border-radius: 5% !important; }
.iti__selected-country-primary:hover { background-color: #F2F2F2 !important; }
.iti--inline-dropdown .iti__dropdown-content { max-width: 355px !important; min-width: 270px !important; }
.iti .iti__selected-dial-code { margin-right: 4px; }
.iti__search-input { padding: 12px 9px 6px 9px !important; }
.iti__arrow { margin-right: 5px !important; }
</style>
