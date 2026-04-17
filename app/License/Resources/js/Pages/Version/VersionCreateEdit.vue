<template>

    <div class="col-sm-12">

        <div class="row" v-if="!hasDataPopulated || loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="version" />

        <div class="card card-light" v-if="hasDataPopulated">

            <div class="card-header">

                <h3 class="card-title">{{lang(title)}}</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <dynamic-select name="product" apiEndpoint="/api/admin/get-products" :multiple="false" :label="trans('product')" :onChange="onChange"
                                    classname="col-sm-6" :value="product_title" optionLabel="product_title" :required="true">

                    </dynamic-select>

                    <text-field :label="lang('version_number')" :value="version_number" type="text" name="version_number"
                                :onChange="onChange" classname="col-sm-6" :required="true">

                    </text-field>
                </div>

                <div class="row">

                    <text-field :label="lang('version_install_file')" :value="version_install_file" type="text" name="version_install_file"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <text-field :label="lang('version_upgrade_file')" :value="version_upgrade_file" type="text" name="version_upgrade_file"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                </div>

                <div class="row">

                    <text-field :label="lang('version_install_query')" :value="version_install_query" type="text" name="version_install_query"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <text-field :label="lang('version_upgrade_query')" :value="version_upgrade_query" type="text" name="version_upgrade_query"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                </div>

                <div class="row">

                    <text-field :label="lang('version_raw_install_query')" :value="version_raw_install_query" rows="7" type="textarea" name="version_raw_install_query"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <text-field :label="lang('version_raw_upgrade_query')" :value="version_raw_upgrade_query" rows="7" type="textarea" name="version_raw_upgrade_query"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>
                </div>

                <div class="row">

                    <text-field :label="lang('version_change_log')" rows="10" :value="version_change_log" type="textarea" name="version_change_log"
                                :onChange="onChange" classname="col-sm-12">

                    </text-field>
                </div>

                <div class="row">

                    <text-field :label="lang('version_install_limit')" :value="version_install_limit" type="text" name="version_install_limit"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <text-field :label="lang('version_upgrade_limit')" :value="version_upgrade_limit" type="text" name="version_upgrade_limit"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                </div>

                <div class="row">

                    <text-field :label="lang('version_comments')" :value="version_comments" type="textarea" name="version_comments"
                                :onChange="onChange" classname="col-sm-6">

                    </text-field>

                    <date-time-picker :label="lang('version_expire_date')" classname="col-sm-6" name="version_expire_date"
                                      :value="version_expire_date" :required="false" :onChange="onChange" >

                    </date-time-picker>
                </div>

                <div class="row">
                    <radio-button :options="radioOptions" :label="lang('version_status')" name="version_status"
                                  :value="version_status" :onChange="onChange" classname="form-group col-sm-6">

                    </radio-button>
                </div>

            </div>

            <div class="card-footer">

                <button class="btn btn-primary" @click="onSubmit()"><i
                    :class="iconClass"></i>&nbsp;&nbsp;{{lang(btnName)}}</button>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios'

import { successHandler, errorHandler } from '../../helpers/responseHandler';

import {getIdFromUrl, lang} from '../../helpers/extraLogics';

import { validateVersionSettings } from '../../helpers/validator/versionValidation'

import { computed }  from 'vue';
import { useStore } from 'vuex';

import TextField from "../../components/Reusable/FormField/TextField.vue";

import RadioButton from "../../components/Reusable/FormField/RadioButton.vue";

import NumberField from "../../components/Reusable/FormField/NumberField.vue";

import DateTimePicker from "../../components/Reusable/FormField/DateTimePicker.vue";

import DatatableDynamicSelect from "../../components/Reusable/FormField/DatatableDynamicSelect.vue";

import moment from "moment";
import store from "../../store";

export default {

    name: 'version-create-edit',

    setup() {

        const store = useStore();

        return {
            // getter
            getApiKey: computed(() => store.getters.getApiKey)
        };
    },

    data() {

        return {

            title: 'create_new_version',

            iconClass: 'fas fa-save',

            btnName: 'save',

            hasDataPopulated: false,

            loading: false,

            radioOptions: [{ name: 'active', value: 1 }, { name: 'inactive', value: 0 }],

            selectedFileType: '',

            product_id: '',

            product_title: '',

            version_number: '',

            version_id: '',

            version_status: 1,

            version_upgrade_limit: '',

            version_install_limit: '',

            version_upgrade_file: '',

            version_install_file: '',

            version_expire_date: '',

            version_install_query: '',

            version_upgrade_query: '',

            version_raw_install_query: '',

            version_raw_upgrade_query: '',

            version_change_log: '',

            version_comments: '',

            apiEndpoint: '',
        }
    },

    beforeMount() {

        const path = window.location.pathname

        this.getValues(path);
    },

    methods: {

        lang,

        getValues(path) {

            const versionId = getIdFromUrl(path)

            if (path.indexOf('edit') >= 0) {

                this.title = 'edit_version'

                this.iconClass = 'fas fa-sync'

                this.btnName = 'update'

                this.hasDataPopulated = false

                this.getInitialValues(versionId);

                this.version_id = versionId

                this.apiEndpoint = '/api/admin/versions/edit';

            } else {

                this.loading = false;

                this.hasDataPopulated = true;

                this.apiEndpoint = '/api/admin/versions/add';
            }
        },

        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/versionView/' + id).then(res => {

                this.loading = false;

                this.hasDataPopulated = true

                this.updateStatesWithData(res.data.data);

            }).catch(error => {

                this.loading = false;
            });
        },

        updateStatesWithData(data) {

            const self = this;

            const stateData = this.$data;

            Object.keys(data).map(key => {

                if (stateData.hasOwnProperty(key)) {

                    self[key] = data[key];
                }
            });

            this.product_title = data.product[0]?.product_title

            this.product_id = data.product[0]?.product_id
        },

        isValid() {

            const { errors, isValid } = validateVersionSettings(this.$data);

            return isValid;
        },

        onChange(value, name) {

            if(name === 'product') {

                this.product_id = value.product_id
                this.product_title = value.product_title

            } else {

                this[name] = value ? value : '';
            }
        },

        onSubmit() {

            if (this.isValid()) {

                this.loading = true

                const data = {};

                if (this.product_id) {

                    data['product_id'] = this.product_id;
                }

                data['api_key_secret'] = this.getApiKey;

                data['product_title'] = this.product_title;

                data['version_number'] = this.version_number;

                data['version_status'] = this.version_status ? 1 : 0;

                data['version_install_file'] = this.version_install_file;

                data['version_install_query'] = this.version_install_query;

                data['version_raw_install_query'] = this.version_raw_install_query;

                data['version_upgrade_file'] = this.version_upgrade_file;

                data['version_upgrade_query'] = this.version_upgrade_query;

                data['version_raw_upgrade_query'] = this.version_raw_upgrade_query

                data['version_install_limit'] = this.version_install_limit;

                data['version_upgrade_limit'] = this.version_upgrade_limit;

                data['version_change_log'] = this.version_change_log;

                data['version_id'] = this.version_id;

                if (this.version_expire_date) {

                    data['version_expire_date'] = moment(this.version_expire_date).format("YYYY-MM-DD");
                }

                data['version_comments'] = this.version_comments;

                axios.post(this.apiEndpoint, data).then(res => {

                    this.loading = false;

                    if (!res.data.api_action_success || res.data.error_detected || res.data.api_error_detected) {

                        store.dispatch('setAlert', { type: 'danger', message: res.data.page_message, component_name: 'version' });

                    } else if(res.data.api_action_success && res.data.action_success) {

                        successHandler({ status: 200, data: { message: res.data.page_message } }, 'version');

                        if (!this.version_id) {

                            setTimeout(() => {

                                this.$router.push('/versions/list')

                            }, 2000)

                        } else {

                            this.getInitialValues(this.version_id)
                        }
                    }

                }).catch(err => {

                    this.loading = false

                    errorHandler(err, 'version')
                });
            }
        }
    },

    components: {
        "dynamic-select": DatatableDynamicSelect,

        "text-field": TextField,

        "radio-button": RadioButton,

        "number-field": NumberField,

        "date-time-picker": DateTimePicker
    }
}
</script>
