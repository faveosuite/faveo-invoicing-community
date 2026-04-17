<template>

    <div class="col-sm-12">

        <div class="alert alert-info">
            <p>Add new license to be used. It's possible to add licenses with and without client's profile.<br><br>With
                client's profile (when client's name and email address are known): select client and product from the
                list, and click the 'Submit'
                button. Client will need to enter his email address during script installation to verify his
                license.<br><br>Without client's profile (when anonymous license needs to be issued): select product
                from the list and enter unique license
                code (entering <b>random</b> will automatically generate a random code). Client will need to enter this
                code during script installation to verify his license.<br><br>If IP address and/or domain is set,
                product will only work on
                specified IP and/or domain. If licensed domain is entered as clientdomain.com, product will work on
                clientdomain.com and clientdomain.com/any/directory. If licensed domain is entered as
                clientdomain.com/path, product will only
                work on clientdomain.com/path. It's possible to add multiple licensed IPs and/or domains by separating
                them with comma (,) symbol. If installations limit is set, client will not be able to run more copies of
                licensed product than
                specified number.<br><br>If expiration date is set, application will stop working after this date
                (expiration date can be updated at any time).</p>
        </div>

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="license" />

        <div class="card card-light" v-if="hasDataPopulated">

            <div class="card-header">

                <h3 class="card-title">{{trans(title)}}</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <dynamic-select name="product" apiEndpoint="/api/admin/viewproducts" :multiple="false" :label="trans('product')" :onChange="onChange"
                                    classname="col-sm-6" :value="product_title" optionLabel="product_title" :required="true">

                    </dynamic-select>

                    <text-field :label="trans('license_code')" :value="license_code" type="text" name="license_code"
                                :onChange="onChange" classname="col-sm-6" :required="true" :disabled="Boolean(client_name)"
                                :showNewButton="!client_name" newBtnName="generate" :onNewButtonClick="generateCode" hint="Either Client's Profile or License Code is Required">

                    </text-field>
                </div>

                <div class="row">

                    <dynamic-select name="client" apiEndpoint="/api/admin/viewClients/0" :multiple="false" :label="trans('client')" :onChange="onChange"
                                    classname="col-sm-6" :value="client_name" optionLabel="full_name" :disabled="Boolean(license_code)" hint="Either Client's Profile or License Code is Required" :required="true">

                    </dynamic-select>

                    <number-field :label="trans('order_number')" :value="license_order_number"
                                  name="license_order_number" :onChange="onChange" classname="col-sm-6">

                    </number-field>

                </div>

                <div class="row">

                    <text-field :label="trans('licensed_ip')" :value="license_ip" type="text" name="license_ip"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <text-field :label="trans('licensed_domain')" :multiple="true" :elements="[]"
                                name="license_domain" classname="col-sm-6" :value="license_domain" :onChange="onChange"
                                :strlength="35" :required="false" :taggable="true" :hint="trans('domain_tip')">
                    </text-field>

                </div>

                <div class="row">

                    <date-picker :label="trans('license_expire_date')" :value="license_expire_date" type="date"
                                 name="license_expire_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">

                    </date-picker>

                    <date-picker :label="trans('license_updates_date')" :value="license_updates_date" type="date"
                                 name="license_updates_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">

                    </date-picker>

                </div>

                <div class="row">

                    <date-picker :label="trans('license_support_date')" :value="license_support_date" type="date"
                                 name="license_support_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">

                    </date-picker>

                    <radio-button :options="domainOptions" :label="trans('license_require_domain')"
                                  name="license_require_domain" :value="license_require_domain ? license_require_domain : 0" :onChange="onChange"
                                  classname="form-group col-sm-6">

                    </radio-button>

                </div>

                <div class="row">

                    <number-field :label="trans('installations_limit')" :value="license_limit" name="license_limit"
                                  :onChange="onChange" classname="col-sm-6">

                    </number-field>

                    <text-field :label="trans('comments')" :value="license_comments" type="textarea"
                                name="license_comments" :onChange="onChange" classname="col-sm-6">

                    </text-field>
                </div>

                <div class="row">

                    <radio-button :options="radioOptions" :label="lang('license_status')" name="license_status"
                                  :value="license_status ? license_status : 0" :onChange="onChange" classname="form-group col-sm-6">

                    </radio-button>

                </div>
            </div>

            <div class="card-footer">

                <button class="btn btn-primary" @click="onSubmit()"><i
                    :class="iconClass"></i>&nbsp;&nbsp;{{trans(btnName)}}</button>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios'

import { successHandler, errorHandler } from '../../helpers/responseHandler';

import { getIdFromUrl, generateRandomString } from '../../helpers/extraLogics';

import { validateLicenseSettings } from "../../helpers/validator/validateLicenseSettings.js";

import moment from 'moment'

import TextField from "../../components/Reusable/FormField/TextField.vue";

import NumberField from "../../components/Reusable/FormField/NumberField.vue";

import StaticSelect from "../../components/Reusable/FormField/StaticSelect.vue";

import DatatableDynamicSelect from "../../components/Reusable/FormField/DatatableDynamicSelect.vue";

import RadioButton from "../../components/Reusable/FormField/RadioButton.vue";

import DateTimePicker from "../../components/Reusable/FormField/DateTimePicker.vue";

import store from "../../store";

export default {

    name: 'license-create-edit',

    data() {

        return {

            title: 'create_new_license',

            iconClass: 'fas fa-save',

            btnName: 'save',

            hasDataPopulated: false,

            loading: false,

            license_status: 1,

            radioOptions: [{ name: 'active', value: 1 }, { name: 'inactive', value: 0 }],
            license_require_domain: 1,

            domainOptions: [{ name: 'yes', value: 1 }, { name: 'no', value: 0 }],

            apiEndpoint: '',

            license_id: '',

            product_id: '',

            product_title: '',

            product_name: '',

            client_id: '',

            client_name: '',

            full_name: '',

            clientOptions: [],

            license_code: '',

            license_order_number: '',

            api_key_secret : '',

            license_ip: '',

            license_domain: '',

            license_limit: '',

            license_expire_date: '',

            license_updates_date: '',

            license_support_date: '',

            license_comments: '',

            moment: moment
        }
    },

    beforeMount() {

        const path = window.location.pathname

        this.getValues(path);

        this.loadData();
    },

    methods: {

        loadData() {

            this.hasDataPopulated = false;

            Promise.all([this.getProducts(), this.getClients()]).then((values) => {

                [this.productOptions, this.clientOptions] = values;

                this.hasDataPopulated = true;

            }).catch(function (error) {

                this.hasDataPopulated = true;
            });
        },

        getProducts() {

            this.loading = true;

            axios.get('/api/admin/viewproducts').then(res => {

                this.productOptions = res.data.data.data.map(data => {

                    data.name = data.product_title;

                    data.id = data.product_id;

                    return data;
                })

                this.loading = false

            }).catch(err=>{});

            return this.productOptions

            this.loading = false
        },

        getClients() {

            axios.get('/api/admin/viewClients').then(res => {

                this.clientOptions = res.data.data.map(data => {

                    data.name = data.full_name;

                    data.id = data.client_id;

                    return data;
                })

                this.loading = false
            }).catch(err=>{});

            return this.clientOptions

            this.loading = false
        },

        getValues(path) {

            this.loading = true;

            this.hasDataPopulated = true

            const licenseId = getIdFromUrl(path)

            this.client_id = store.getters.getUserData.client_id

            if (path.indexOf('edit') >= 0) {

                this.title = 'edit_license'

                this.iconClass = 'fas fa-sync'

                this.btnName = 'update'

                this.hasDataPopulated = false

                this.getInitialValues(licenseId);

                this.license_id = licenseId;

                this.apiEndpoint = '/api/admin/license/edit';

            } else {

                this.loading = false;

                this.hasDataPopulated = true;

                this.apiEndpoint = '/api/admin/license/add';
            }
        },

        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/license/' + id).then(res => {

                let resData = res.data.data;

                let licenseData = res.data.data.license;

                licenseData['api_key_secret'] = licenseData.api_key_secret ? licenseData.api_key_secret.split(','): '';

                // licenseData['license_domain'] = licenseData.license_domain ? licenseData.license_domain.split(',') : '';

                licenseData['license_expire_date'] = licenseData.license_expire_date ? new Date(moment(licenseData.license_expire_date).format("MM-DD-YYYY")) : '';

                licenseData['license_updates_date'] = licenseData.license_updates_date ? new Date(moment(licenseData.license_updates_date).format("MM-DD-YYYY")) : '';

                licenseData['license_support_date'] = licenseData.license_support_date ? new Date(moment(licenseData.license_support_date).format("MM-DD-YYYY")) : '';

                this.updateStatesWithData(resData);

                this.loading = false;

            }).catch(error => {

                this.loading = false;
            });
        },

        updateStatesWithData(data) {

            const self = this;

            const stateData = this.$data;

            Object.keys(data.license).map(key => {

                if (stateData.hasOwnProperty(key)) {

                    self[key] = data[key];
                }
            });

            // this.product_id = { id : data.product_name[0].product_id , name : data.product_name[0].product_title }
            this.product_id = data.product_name[0].product_id
            this.product_title = data.product_name[0].product_title

            this.license_id = data.license.id;

            this.api_key_secret = data.license.api_key_secret;

            this.license_code = data.license.license_code;

            this.license_order_number = data.license.license_order_number;

            this.license_limit = data.license.license_limit;

            this.license_ip = data.license.license_ip;

            this.license_comments = data.license.license_comments;

            this.license_require_domain = data.license.license_require_domain;

            this.license_status = data.license.license_status ;

            this.license_domain = data.license.license_domain;

            this.license_expire_date = data.license.license_expire_date;

            this.license_updates_date = data.license.license_updates_date;

            this.license_support_date = data.license.license_support_date;

        },

        isValid() {

            const { errors, isValid } = validateLicenseSettings(this.$data);

            return isValid;
        },

        onChange(value, name) {

            if(name === 'product') {

                this.product_id = value.product_id
                this.product_title = value.product_title
            } else if(name === 'client') {

                this.client_id = value ? value.client_id : this.client_id
                this.client_name = value ? value.full_name : ''
            }
            else {

                this[name] = value ? value : '';
            }
        },

        generateCode() {
            this.license_code = generateRandomString(16);
        },

        onSubmit() {

            if (this.isValid()) {

                this.loading = true

                const data = {};

                if (this.license_id) {

                    data['id'] = this.license_id;
                }

                data['product_id'] = this.product_id ? this.product_id : '';

                data['license_status'] = this.license_status ? 1 : 0;

                data['license_require_domain'] = this.license_require_domain ? 1 : 0;

                if(this.client_id && !this.license_code) {
                    data['client_id'] = this.client_id
                }

                if(this.client_name) {
                    data['client_name'] = this.client_name
                }

                if (this.license_order_number) { data['license_order_number'] = this.license_order_number; }

                data['license_ip'] = this.license_ip;

                data['license_domain'] = this.license_domain;

                if (this.license_limit) { data['license_limit'] = this.license_limit; }

                data['license_comments'] = this.license_comments;

                if (this.license_expire_date) {
                    data['license_expire_date'] = moment(this.license_expire_date).format("YYYY-MM-DD");
                }

                if (this.license_updates_date) {
                    data['license_updates_date'] = moment(this.license_updates_date).format("YYYY-MM-DD");
                }

                if (this.license_support_date) {
                    data['license_support_date'] = moment(this.license_support_date).format("YYYY-MM-DD");
                }

                data['license_code'] = this.license_code;

                axios.post(this.apiEndpoint, data).then(res => {

                    this.loading = false

                    successHandler(res, 'license')

                    if (!this.license_id) {

                        setTimeout(() => {

                            this.$router.push('/licenses')

                        }, 2000)

                    } else {

                        this.getInitialValues(this.license_id)
                    }

                }).catch(err => {

                    this.loading = false

                    errorHandler(err, 'license')
                });
            }
        }
    },

    components: {

        "text-field": TextField,

        "number-field": NumberField,

        "static-select": StaticSelect,

        "dynamic-select": DatatableDynamicSelect,

        "radio-button": RadioButton,

        "date-picker": DateTimePicker
    }
}
</script>
