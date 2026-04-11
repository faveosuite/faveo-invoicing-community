<template>

    <div class="col-sm-12">

        <alert componentName="edit_profile"/>

        <div class="row" v-if="!hasDataPopulated || loading">

            <custom-loader :duration="4000"></custom-loader>

        </div>

        <div class="row">

            <div class="col-md-6" v-if="hasDataPopulated">

                <div class="card card-light ">

                    <div class="card-header">

                        <h3 class="card-title text-bold">{{lang('profile')}}</h3>

                    </div>

                    <div class="card-body">

                        <div class="text-center">

                            <image-upload :label="lang('profile_pic')" :value="client_profile_pic" componentName="edit_profile" name="client_profile_pic" :onChange="onChange"
                                          :labelStyle="labelStyle" :labelCss="labelCss" buttonName="change">

                            </image-upload>
                        </div>

                        <text-field :label="lang('first_name')" :value="client_fname" type="text" name="client_fname"
                                    :onChange="onChange" :placehold="lang('enter_a_value')" :required="true" :autofocus="true">

                        </text-field>

                        <text-field :label="lang('last_name')" :value="client_lname" type="text" name="client_lname"
                                    :onChange="onChange" :required="true" :placehold="lang('enter_a_value')">

                        </text-field>

                        <text-field :label="lang('user_name')" :value="client_username" type="text" name="client_username"
                                    :onChange="onChange" :required="true" :placehold="lang('enter_a_value')">

                        </text-field>

                        <text-field :label="lang('email_address')" :value="client_email" type="text" name="client_email" :onChange="onChange" :placehold="lang('enter_email_addresses')" :disabled="true">

                        </text-field>

                        <dynamic-select name="client_timezone_id" apiEndpoint="/api/admin/timezones" :multiple="false" label="Timezone Settings" :onChange="onChange"
                                        :value="timezone" optionLabel="location" :required="true">

                        </dynamic-select>

                        <div class="row">

                            <phoneWithCountryCode id="client_mobile" classname="col-sm-9" name="client_mobile" :onChange="onChange"
                                                  :value="client_mobile" :countryCode="phone_country_code" :countryIso="client_iso2"
                                                  @countCode="getPCountCode" @countIso="getPCountIso" labelName="phone_number"
                                                  @validPhoneNumber="checkValidity" fieldType="FIXED_LINE_OR_MOBILE">

                            </phoneWithCountryCode>

                        </div>

                    </div>

                    <div class="card-footer">

                        <button class="btn btn-primary" @click="onSubmit()" :disabled="isDisabled">

                            <i class="fas fa-sync"></i> {{lang('update')}}
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6" v-if="hasDataPopulated">

                <div class="card card-light ">

                    <div class="card-header">

                        <h3 class="card-title text-bold">{{lang('change_password')}}</h3>
                    </div>

                    <div class="card-body">

                        <text-field :label="lang('old_password')" :value="old_password" type="password" name="old_password"
                                    :onChange="onChange" :placehold="lang('enter_a_value')" :required="true">

                        </text-field>
                        <text-field :label="lang('new_password')" :value="new_password" type="password" name="new_password"
                                    :onChange="onChange" :placehold="lang('enter_a_value')" :required="true" id="new_password">

                        </text-field>
                        <text-field :label="lang('confirm_password')" :value="confirm_password" type="password"
                                    name="confirm_password" :onChange="onChange" :placehold="lang('enter_a_value')" :required="true" id="confirm_password">

                        </text-field>
                    </div>

                    <div class="card-footer">

                        <button class="btn btn-primary" @click="onUpdatePassword()" :disabled="passDisabled">

                            <i class="fas fa-sync"></i> {{lang('update')}}
                        </button>
                    </div>
                </div>

                <div class="card card-light ">

                    <div class="card-header">

                        <h3 class="card-title text-bold">{{lang('2fa_setup')}}</h3>
                    </div>

                    <div class="card-body">

                        <div class="row">

                            <div class="col-md-9">

								<span>

									<img class="img-responsive img-rounder img-sm" :src="basePath()+'/themes/default/img/authenticator.png'" alt="A"
                                         id="auth_img">&nbsp;{{two_factor ? '2-Step Verification is ON since '+ getDate  : lang('authenticator_app')}}
								</span>
                            </div>

                            <div class="col-md-3">

                                <button v-if="!two_factor" type="button" class="btn btn-primary float-end" @click="showModal = true">

                                    <i class="fas fa-toggle-on"></i> {{lang('turn_on')}}

                                </button>

                                <button v-if="two_factor" type="button" class="btn btn-secondary float-end" @click="removeModal = true">

                                    <i class="fas fa-power-off"></i> {{lang('turn_off')}}

                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <transition name="modal">

                    <barcode-modal v-if="showModal" @updateEditData="getData()" :onClose="onClose" :showModal="showModal">

                    </barcode-modal>
                </transition>

                <transition name="modal">

                    <remove-modal v-if="removeModal" @updateEditData="getData()" :onClose="onClose" :showModal="removeModal" alertName="edit_profile">

                    </remove-modal>
                </transition>

            </div>
        </div>
    </div>

</template>

<script>

import TextField from "../../components/Reusable/FormField/TextField.vue";
import {lang, formatDateTime} from "../../helpers/extraLogics";
import NumberField from "../../components/Reusable/FormField/NumberField.vue";
import ImageUpload from "../../components/Reusable/FormField/ImageUpload.vue";
import PhoneWithCountryCode from "../../components/Reusable/FormField/PhoneWithCountryCode.vue";
import BarcodeModal from "./BarcodeModal.vue";
import RemoveVerification from "./RemoveVerification.vue";
import {validateProfileSettings} from "../../helpers/validator/validateProfileSettings";
import {errorHandler, successHandler} from "../../helpers/responseHandler";
import {validatePasswordSettings} from "../../helpers/validator/passwordSettings";
import {useStore} from "vuex";
import DatatableDynamicSelect from "../../components/Reusable/FormField/DatatableDynamicSelect.vue";

export default {

    name: "edit_profile",

    setup() {
        const store = useStore();

        return {
            code : store.getters.getUserData.client_mobile_code,
            client_iso : store.getters.getUserData.client_iso2
        }
    },

    props : {
    },

    data() {

      return {

          loading: false,

          hasDataPopulated : true,

          isDisabled : false,

          passDisabled : false,




          two_factor: false,

          client_fname: '',

          client_lname: '',

          client_username: '',

          client_email: '',

          client_mobile: '',

          phone_country_code : this.code,

          client_iso2 : this.client_iso,

          google2fa_activation_date: '',

          country_code: 91,

          client_profile_pic:'',

          labelStyle : { display : 'none' },

          labelCss : { visibility : 'hidden', margin : 'auto'},

          tooltip : '',

          showModal: false,

          removeModal : false,

          styleObj : { background : 'none' },

          iso: '',

          selectedImage : '',

          old_password: '',

          new_password: '',

          confirm_password: '',

          client_timezone_id: '',

          timezone: '',

          phoneValidityStatus: { client_mobile: true }
      }
    },
    methods: {

        lang,

        getData(from){

            this.loading = true;

            axios.get('/api/admin/profile/info').then(res=>{

                this.updateStatesWithData(res.data.data);

                if(from === 'update') {

                    const payload = {
                        profile_pic: res.data.data.client_profile_pic,
                        client_mobile_code: res.data.data.client_mobile_code,
                        client_iso2: res.data.data.client_iso2,
                        client_fname: res.data.data.client_fname,
                        client_lname: res.data.data.client_lname,
                        client_email: res.data.data.client_email,
                        client_timezone_id: res.data.data.client_timezone_id,
                    }

                    this.$store.dispatch('setUserData', payload)
                }

                this.loading = false;

                this.hasDataPopulated = true;

            }).catch(error=>{

                this.loading = false;

                this.hasDataPopulated = true;

            });
        },

        updateStatesWithData(data){

            const self = this;

            const stateData = this.$data;

            Object.keys(data).map(key => {

                if (stateData.hasOwnProperty(key)) {

                    self[key] = data[key];
                }

            });

            this.mobile = this.mobile === 'Not available' ? '' : this.mobile;

            this.country_code = this.country_code === '' ? 91 : this.country_code;

            this.phone_number = this.phone_number === 'Not available' ? '' : this.phone_number;

            this.phone_country_code = this.phone_country_code === '' ? 91 : this.phone_country_code;

            this.two_factor = data.is_2fa_enabled;

            this.timezone = data.timezone.timezone_name;
        },

        onChange(value, name) {

           if(name === 'client_profile_pic') {
               this.client_profile_pic = value.image;

               this.selectedImage = value;

           }  else {

               this[name] = value
           }

            // Validate client_timezone_id
            if (name === 'client_timezone_id' && value === null) {
                this.client_timezone_id = ''
            }
        },

        getPCountCode(value){

            this.phone_country_code = value;
        },

        getPCountIso(value){

            this.client_iso2 = value;
        },

        checkValidity(name, value) {

            this.phoneValidityStatus[name] = value;
        },

        isValid(){
            const {errors, isValid} = validateProfileSettings(this.$data);

            return isValid;

        },

        isPasswordValid(){

            const {errors, isValid} = validatePasswordSettings(this.$data);

            return isValid;

        },

        onSubmit(){

            if(this.isValid() && this.isPhoneValid){

                this.isDisabled=true;

                this.loading=true;

                var fd = new FormData();

                fd.append('client_fname', this.client_fname);

                fd.append('client_lname', this.client_lname);

                fd.append('client_email', this.client_email ? this.client_email : null);

                fd.append('client_username', this.client_username);

                fd.append('client_iso2', this.client_iso2)

                fd.append('client_mobile_code', this.phone_country_code ? this.phone_country_code : null);

                fd.append('client_mobile', this.client_mobile ? this.client_mobile : '');

                fd.append('client_timezone_id', this.client_timezone_id.id || this.client_timezone_id);

                

                if(this.selectedImage){
                    fd.append('client_profile_pic', this.selectedImage.file,this.selectedImage.name);
                } else {
                    fd.append('client_profile_pic', null);
                }

                if(this.types !== ''){

                    for(var i in this.types){

                        fd.append('type['+i+']', this.types[i].id);
                    }
                }

                fd.append('_method', "PATCH");

                axios.post('/api/admin/profile', fd).then(res=> {

                    successHandler(res,'edit_profile');

                    this.isDisabled=false;

                    this.loading=false;

                    this.getData('update');

                }).catch(error=>{

                    this.loading=false;

                    this.isDisabled=false;

                    errorHandler(error,'edit_profile')
                })
            }
        },

        onUpdatePassword(){

            var regex = /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{6,20}$/;

            var message = regex.exec(this.new_password);

            if(this.isPasswordValid()){

                if(this.old_password !== this.new_password){

                    if(message){

                        if(this.new_password === this.confirm_password){

                            this.passDisabled=true;

                            this.loading=true;

                            var fd = new FormData();

                            fd.append('old_password',this.old_password);

                            fd.append('new_password',this.new_password);

                            fd.append('confirm_password',this.confirm_password);

                            fd.append('_method', "PATCH");

                            axios.post('/api/admin/password',fd).then(res=> {

                                this.old_password = '';

                                this.new_password = '';

                                this.confirm_password = '';

                                this.loading=false;

                                this.passDisabled=false;

                                successHandler(res,'edit_profile');

                            }).catch(error=>{

                                this.loading=false;

                                this.passDisabled=false;

                                errorHandler(error,'edit_profile')
                            })
                        }else {

                            this.$store.dispatch('setValidationError', {'confirm_password' : 'Password does not match'})
                        }
                    } else {

                        this.$store.dispatch('setValidationError', {'new_password' : 'Password must have 8 characters and contain at least one Uppercase, one lowercase, one number and one special character'})
                    }
                }else {

                    this.$store.dispatch('setValidationError', {'new_password' : 'new password is same as old. Please choose a different password'})
                }
            }
        },

        onClose(){

            this.showModal = false;

            this.removeModal = false;

            this.$store.dispatch('unsetValidationError');
        },
    },
    computed: {

        getDate() {

            return this.google2fa_activation_date
        },

        isPhoneValid() {

            return this.phoneValidityStatus.client_mobile;
        }
    },
    beforeMount() {
        this.getData()
    },

    components: {
        'dynamic-select' : DatatableDynamicSelect,
        'number-field' : NumberField,
        'text-field' : TextField,
        'image-upload' : ImageUpload,
        'phoneWithCountryCode': PhoneWithCountryCode,
        'barcode-modal': BarcodeModal,
        'remove-modal': RemoveVerification
    }
}
</script>


<style scoped>

.img-sm{
    height: 1.875rem;
    width: 1.875rem;
}

</style>
