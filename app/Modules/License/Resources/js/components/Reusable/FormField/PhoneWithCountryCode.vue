<template>
    <!-- Form field template with a label and input for phone number with country code -->
    <form-field-template :classname="classname" :label="lang(labelName)" :name="name">

        <!-- Phone number input field with validation and error display -->
        <div class="intl-tel-input allow-dropdown separate-dial-code iti-sdc-3 telephone-input">

            <input class="form-control telephone-input-field" ref="phoneInput" :class="{'has-error': showError}"
                   type="tel" v-model="changedValue" v-on:input="onChange(changedValue, name)" @keypress="isNumber" :style="inputStyle" id="id"/>

            <div v-if="showError" class="error-block is-danger">{{lang(errorMsg)}}</div>
        </div>
    </form-field-template>
</template>

<script type="text/javascript">

import intlTelInput from "intl-tel-input/intlTelInputWithUtils"
import FormFieldTemplate from "./FormFieldTemplate.vue";
import axios from 'axios';
import {findObjectByKey} from "../../../helpers/extraLogics";

export default {
    name: 'PhoneWithCountryCode',
    data() {
        return {
            // Instance of intl-tel-input
            iti: {},

            // Phone number input value
            phone: '',

            // Changed value of the phone number input
            changedValue: this.value,

            // Default country code for the phone number input
            defaultCountryCode: 'IN',

            // Error message to display when the phone number is invalid
            errorMsg: '',

            // Flag to show or hide the error message
            showError: false,

            // Flag to show or hide the field
            ShowField: false,

            // Flag to indicate if the component is mounted
            isMounted: false,

        };
    },
    components: {

        'form-field-template': FormFieldTemplate
    },

    props: {

        // Label name for the form field
        labelName: {type: String, default: 'phone_number'},

        // Class name for the form field
        classname: {type: String, default: ''},

        // Value of the phone number input
        value: {type: [String, Number, null], required: true},

        // Name of the form field
        name: {type: String, required: true},

        // Function to call when the phone number input changes
        onChange: {type: Function, required: true},

        // ISO code of the country for the phone number input
        countryIso: {type: String, default: ''},

        // Country code for the phone number input
        countryCode: {type: [Number, String ,null], required: true},

        // Style object for the phone number input
        inputStyle: { type: Object, default: () => {} },

        /**
         * Default field type is FIXED_LINE_OR_MOBILE and field type can be one of the following like
         * FIXED_LINE, MOBILE, FIXED_LINE_OR_MOBILE, TOLL_FREE, PREMIUM_RATE, SHARED_COST, VOIP, PERSONAL_NUMBER, PAGER, UAN, VOICEMAIL
         **/

        fieldType: {type: String, default: 'FIXED_LINE_OR_MOBILE'},
    },

    mounted() {

        // Initialize the intl-tel-input instance
        const input = this.$refs.phoneInput;

        if(input) {
            this.iti = intlTelInput(input, {

                initialCountry: this.countryIso ? this.countryIso : (this.countryCode ? this.getIsoByCountryCode(this.countryCode) : 'auto'),

                geoIpLookup: this.fetchGeoIpCountryCode,

                validationNumberType: this.fieldType,

                placeholderNumberType: this.fieldType,

                allowDropdown: true,

                separateDialCode: true,

                i18n: 'en',

                showFlags: true,

                formatAsYouType: false,

                strictMode: true,

                formatOnDisplay: false,

                nationalMode: false,

                excludeCountries: ['ax'],

                customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {

                    return selectedCountryPlaceholder;
                },
            });

            input.addEventListener('countrychange', this.updatePhone);

            // Emit events if initialCountry is set to 'auto'
            if (this.iti.options.initialCountry === 'auto') {

                this.EmitIsoAndDailCodes();
            }
        }
    },

    watch: {
        // Watcher for the value prop to validate the phone number
        value(newVal) {
            this.changedValue = newVal;
            if (!this.isMounted) return;
            const isValid = newVal === '' || this.iti.isValidNumber();
            this.$emit('validPhoneNumber', this.name, isValid);
            this.showError = !isValid;
            this.errorMsg = isValid ? '' : 'invalid_phone_number';
        }
    },

    updated(){
        this.ShowField = true;
        this.isMounted = true;
    },

    methods: {
        // Update the phone number input when the country changes
        updatePhone() {

            if(this.ShowField){
                this.changedValue = ''; // Clear the input field when the country changes
            }
            this.EmitIsoAndDailCodes();
        },

        // Validate if the input is a number
        isNumber(evt) {
            evt = evt || window.event;
            var charCode = evt.which || evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                evt.preventDefault();
            } else {
                return true;
            }
        },

        //Fetch the default country code based on the user's IP address
        fetchGeoIpCountryCode(success = () => {
        }) {
            axios.get("https://ipapi.co/json")
                .then(res => {
                    this.defaultCountryCode = res.data.country_code;
                    success(res.data.country_code);
                })
                .catch(() => {
                    this.defaultCountryCode = 'IN';
                    success('IN');
                });
        },

        // Get ISO code by country code
        getIsoByCountryCode(countryCode) {
            let countryData = [];

            countryData = intlTelInput.getCountryData();

            let country = findObjectByKey(countryData, 'dialCode', countryCode);

            return country ? country.iso2 : 'auto';
        },

        // Emit the country code and ISO code when the country changes
        EmitIsoAndDailCodes(){
            // Emit the country code and ISO code when the country changes
            let selectedCountry = this.iti.getSelectedCountryData();
            this.$emit('countCode', selectedCountry.dialCode);
            this.$emit('countIso', selectedCountry.iso2);
        }
    },

    beforeUnmount() {

        // Destroy the intl-tel-input instance before the component is unmounted
        this.iti?.destroy();
        this.iti = null;
        this.showField = false;
    }
};
</script>

<style>

@import 'intl-tel-input/build/css/intlTelInput.css';

.iti--allow-dropdown {
    width: 100% !important;
}

.telephone-input {
    width: 100%;
}

.iti__selected-country {
    padding: 5px !important;
    background-color: #F2F2F2 !important;
    outline: none !important;
    border-radius: 5% !important;
}

.iti__selected-country-primary:hover {
    background-color: #F2F2F2 !important;
}

.iti--inline-dropdown .iti__dropdown-content {
    max-width: 355px !important;
    min-width: 270px !important;
}

.iti .iti__selected-dial-code {
    margin-right: 4px;
}

.iti__search-input {
    padding: 12px 9px 6px 9px !important;
}

.iti__arrow {
    margin-right: 5px !important;
}
</style>
