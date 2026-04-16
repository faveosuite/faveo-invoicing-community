<template>

    <div v-if="siteKey" id="cap_cha">

            <vue-recaptcha ref="invisibleRecaptcha"
                           name="form-recaptcha"
                           v-model="recaptchaVerified"
                           @verify="markRecaptchaAsVerified"
                           @render="renderMethod"
                           @expired="onExpired"
                           :sitekey="siteKey ? siteKey : ''"
                           :size="version === 'v2' ? 'normal' : 'invisible'">
            </vue-recaptcha>
    </div>
</template>
<script>

import { VueRecaptcha } from 'vue-recaptcha';

export default {

    name : 'recaptcha-field',

    description : 'Recaptcha Field component',

    props: {

        siteKeyValue : { type : String, default : '' },

        captchaVersion : { type : String, default : '' },

        from : { type : String, default : ''},

        node: {type: Object, default : ()=> {}},

        category : {type: String, default: 'ticket'},

        verifyCaptcha : { type : Function, default :()=>{} }
    },

    data() {

        return {

            recaptchaVerified : "",
        }
    },

    components: {

        VueRecaptcha
    },

    computed: {

        siteKey() {

            return this.siteKeyValue ? this.siteKeyValue : this.recaptchaSiteKey
        },

        version() {

            return this.captchaVersion ? this.captchaVersion : this.recaptchaVersion
        }
    },

    methods: {

        markRecaptchaAsVerified(response) {

            this.recaptchaVerified = response;

            this.verifyCaptcha(response);
        },

        renderMethod() {

            if(this.siteKey)
            {
                this.$refs.invisibleRecaptcha.execute();
            }
        },

        onExpired() {

            this.verifyCaptcha('');
        }
    }
};
</script>

<style>
#faveo-form-client-panel #cap_cha,.faveo-form #cap_cha{ margin-left: -10px; }

.modal-body #cap_cha { margin-left: -20px !important; }
</style>
