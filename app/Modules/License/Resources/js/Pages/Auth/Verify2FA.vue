<template>

	<transition name="page" mode="out-in">

		<div class="login-page">

			<div  id="content" class="login-box" >

				<alert componentName="2fa"/>

                <div class="card">

                    <div class="card-body login-card-body">

                        <h3 class="login-box-msg text-dark" id="head3">{{ showRecovery ? lang('2_factor_auth') : lang('recovery_factor_auth') }}</h3>

                        <text-field :label="lang('enter_code')" :value="otp" id="2fa_otp" :autofocus="true"
                                    type="text" name="otp" :keyupListener="triggerEvent" :onChange="onChange" classname="" :required="true">

                        </text-field>

                        <p>{{ showRecovery ? lang('2fa-message') : lang('recovery_factor_message') }}</p>

                        <div v-if="loading">

                            <custom-loader :animation-duration="4000" :size="50"></custom-loader>
                        </div>

                        <div class="row">

                            <div class="col-sm-8" v-if="showRecovery">

                                <span><b>Having problems?</b></span>

                                <p><a href="javascript:;" @click="showRecovery=false">Login using recovery code</a></p>
                            </div>

                            <div class="col-sm-8" v-if="!showRecovery">

                                <p><a href="javascript:;" @click="showRecovery=true">Login using Authenticator passcode</a></p>
                            </div>

                            <div class="col-sm-4">

                                <button class="btn btn-block btn-primary" @click="onSubmit()" :disabled="!otp"
                                        id="2fa_otp_verify">
                                    <i class="fa fa-check"> </i> {{ lang('verify') }}

                                </button>
                            </div>
                        </div>
                    </div>

                </div>

			</div>
		</div>
	</transition>
</template>

<script>

    import axios from 'axios'
    import { lang } from '../../helpers/extraLogics'
    import TextField from "../../components/Reusable/FormField/TextField.vue";
    import {useStore} from "vuex";
    import {computed} from "vue";
    import store from "../../store";

	export default {

		name : 'verify-2fa',

		description : 'Two-factor Authentication component',

		props : {

			layout : { type : Object, default : ()=>{}},

			auth : { type : Object, default : ()=>{}},

			pp : { type : [Object, String], default : ''},

			remember : { type : Boolean, default : false},

		},

		data() {
			return {

				loading:false,

		    	otp: "",

                p_auth : "",

                siteKey: "",

                recaptchaToken: "",

				isDisabled:false,

				showRecovery : true
			}
		},

        setup() {

            const store = useStore();

            return {
                getUserToken: computed(() => store.getters.getUserToken)
            };
        },

		beforeMount(){

          let params = new URLSearchParams(window.location.search);
          let p_auth_value = params.get("PPAuth");
          if(p_auth_value) {
            let splitVal = p_auth_value.split(',');

            let objVal = {};

            objVal[splitVal[0]] = splitVal[1];

            this.p_auth =objVal;
          } else {
              this.p_auth = this.pp ? JSON.parse(this.pp) : '';
          }

          this.initializeRecaptcha();

		},

		watch : {

			showRecovery(newValue,oldValue){

				this.otp = '';

				this.$store.dispatch('unsetValidationError');

				this.$store.dispatch('unsetAlert');
			}
		},

		methods: {

            lang,

            onChange(value, name) {

                this[name] = value;
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
                        component_name: '2fa'
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

    		if(!this.showRecovery) {

    			return this.verifyFactor();
    		}

			this.isDisabled=true;

			this.loading=true;

			const data = {};

			data['totp'] = this.otp;

			data['PPAuth'] = this.p_auth ? this.p_auth : '';

            if(this.siteKey){
                data['g-recaptcha-response'] = this.recaptchaToken
            }

			axios.post('/api/verify2fa',data).then(response =>{

				this.afterSuccess(response);

			}).catch(error=>{

				this.afterFailure(error)
			})
	   },

	   verifyFactor() {

          this.isDisabled = true;

          this.loading = true;

          const data = {};

          data['recovery_code'] = this.otp;

          data['PPAuth'] = this.p_auth;

           if(this.siteKey){
               data['g-recaptcha-response'] = this.recaptchaToken
           }

          axios.post('/api/verify-recovery-code', data).then(response => {

            this.afterSuccess(response);

          }).catch(error => {

            this.afterFailure(error)
          })

	   },

	   afterSuccess(response) {

          this.isDisabled = false;

          this.loading = false;

           const authToken = response.data.data.token;

           axios.defaults.headers.common['Authorization'] = `Bearer ${authToken}`;

           this.$store.dispatch('setLoggedInUserToken', authToken);

           

           

           this.$store.dispatch('setUserInfo', response.data.data.user);

           //to hide recaptcha badge
           if(this.siteKey) {
               let element = document.getElementsByClassName('grecaptcha-badge');
               element[0].setAttribute('id', 'grecaptcha_badge');
               document.getElementById('grecaptcha_badge').style.visibility = 'hidden';
           }

           window.location.href = this.basePath() + (this.getUserToken ? '/dashboard' : '/login');

       },

	  afterFailure(error) {

			this.isDisabled=false;

			this.loading=false;

            store.dispatch('setAlert', { type: 'danger', message: error.response.data.message, component_name: '2fa' })

            this.initializeRecaptcha();
	  },

      triggerEvent(event) {
        var key = event.which || event.keyCode;
        if (key === 13) // 13 is enter
        if(document.getElementById("2fa_otp").value !== "")
        {
          this.onSubmit();
        }
      },
	},

		components:{

			"text-field": TextField,
		}
	};
</script>

<style scoped>

	.fa_align{
		direction: rtl;
	}

	#head3{
		margin-top: 0px !important;
	}
</style>
