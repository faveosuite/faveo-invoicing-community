<template>

    <div class="login-page">

        <div class="login-logo">

            <image-element id="profile-pic" width="100px" height="100px" :classes="['img-responsive', 'img-click']" :sourceUrl="admin"></image-element>
        </div>

        <div class="login-box">

            <div class="card">

                <div class="card-body login-card-body">

                    <p class="login-box-msg">{{lang('reset_password')}}</p>

                    <alert componentName="reset"></alert>

                    <div v-if="loading" class="mt-4 mb-4">

                        <loader></loader>
                    </div>

                    <template v-if="!loading">

                        <text-field :labelStyle="labelStyle" :label="lang('password')" :value="password" type="password"
                            name="password" :keyupListener="triggerEvent" :onChange="onChange" placehold="New Password"
                             :required="true">

                        </text-field>

                        <text-field :labelStyle="labelStyle" :label="lang('password_confirmation')" :value="password_confirmation" type="password" name="password_confirmation"
                            :onChange="onChange" placehold="Confirm Password" :keyupListener="triggerEvent" id="password_confirmation"
                            :required="true">

                        </text-field>

                        <div class="social-auth-links text-center mb-1">

                            <a href="javascript:;" class="btn btn-block btn-primary" @click="onSubmit()">

                                <i class="fas fa-sync"></i>&nbsp;&nbsp;{{lang('reset_password')}}
                            </a>
                        </div>

                        <p class="mb-1">

                            <router-link to="/login">{{lang('go_to_login')}}</router-link>
                        </p>

                    </template>
                </div>
            </div>
        </div>
    </div>
</template>
<script>

    import { computed }  from 'vue';
    import { useStore } from 'vuex';

    import { errorHandler, successHandler } from '../../helpers/responseHandler'

    import { validateResetSettings } from "../../helpers/validator/resetRules";

    import axios from 'axios'

    import ImageElement from "../../components/Reusable/ImageElement.vue";

    import TextField from "../../components/Reusable/FormField/TextField.vue";
    import {lang} from "../../helpers/extraLogics";

    export default {

        name: 'reset',

        setup() {

            const store = useStore();

            return {
                // getter
                getUserToken: computed(() => store.getters.getUserToken)
            };
        },

        props : {
            generalSetting : {type : Object, default : () => {}},
        },

        data() {

            return {

                password: '',

                password_confirmation:'',

                path:'',

                siteKey: "",

                recaptchaToken: "",

                admin: this.generalSetting.client_logo,

                labelStyle: { display: 'none' },

                token:'',

                userEmail : '',

                loading: false,
            }
        },

        beforeMount() {

            if (this.getUserToken) {

                this.$router.push({ name: 'Login' }).catch(err => { })
            } else {

                this.initializeRecaptcha();
            }
        },

        mounted(){

            this.userEmail = location.search.split('=');

            this.user = decodeURIComponent(this.userEmail[this.userEmail.length-1]);

            this.loading = false;
        },

        methods: {

            onChange(value, name) {

                this[name] = value;
            },

            isValid() {

                const { errors, isValid } = validateResetSettings(this.$data);

                return isValid;

            },

            triggerEvent(event) {

                var key = event.which || event.keyCode;

                if (key === 13) { // 13 is enter

                    this.onSubmit();
                }
            },

            async initializeRecaptcha() {
                try {
                    await this.getSiteKey();
                    await this.loadRecaptchaScript();
                    this.recaptchaToken = await this.generateRecaptchaToken();
                } catch (error) {
                    this.$store.dispatch('setAlert', {
                        message: lang('recaptcha_not_loaded'),
                        type: 'danger',
                        component_name: 'reset'
                    });
                }
            },

            loadRecaptchaScript() {
                return new Promise((resolve, reject) => {
                    const script = document.createElement('script');
                    script.src = 'https://www.google.com/recaptcha/api.js?render=' + this.siteKey;
                    script.onload = resolve;
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            },

            generateRecaptchaToken() {
                return new Promise((resolve, reject) => {
                    grecaptcha.ready(() => {
                        grecaptcha.execute(this.siteKey, {action: 'submit'}).then(resolve).catch(reject);
                    });
                });
            },

            async getSiteKey() {
                await axios.get('/api/recaptchaStatus')
                    .then((res) => this.siteKey = res.data.site_key)
                    .catch(() => {
                        // handle error if needed
                    });
            },

            onSubmit() {

                if(this.isValid()){

                    if(this.password === this.password_confirmation){

                        this.loading = true;

                        this.path= location.pathname.split('/');

                        this.token = this.path[this.path.length-1];

                        const data = {token : this.token, password : this.password_confirmation}

                        data['password'] = this.password

                        data['password_confirmation'] = this.password_confirmation

                        if(this.siteKey){
                            data['g-recaptcha-response'] = this.recaptchaToken
                        }

                        axios.post('api/reset',data).then(res=>{

                            this.loading = false;

                            successHandler(res,'reset');

                            setTimeout(()=>{

                                this.$router.push({ path:'/login/',name: 'login'});
                            },3000)

                        }).catch(error=>{

                            errorHandler(error,'reset');

                            this.initializeRecaptcha();

                            this.loading = false;
                        })
                    }
                    else {

                        this.$store.dispatch('setValidationError', {'password_confirmation' : 'Password does not match'})
                    }
                }
            }
        },

        components: {

            "text-field": TextField,

            "image-element": ImageElement
        }
    };
</script>
