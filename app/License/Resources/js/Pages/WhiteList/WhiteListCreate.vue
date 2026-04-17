<template>

    <div class="col-sm-12">

        <div class="row" v-if="!hasDataPopulated || loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="whitelist" />

        <div class="card card-light" v-if="hasDataPopulated">

            <div class="card-header">

                <h3 class="card-title">{{lang(title)}}</h3>
            </div>

            <div class="card-body">

                <div class="row">

                    <text-field :label="lang('ip_address')" :value="whitelist_host_ip" :onChange="onChange" name="whitelist_host_ip"
                                type="text" classname="col-sm-6" :required="true">

                    </text-field>

                    <text-field :label="lang('comments')" type="text" :value="whitelist_host_comments" :onChange="onChange"
                                name="whitelist_host_comments" classname="col-sm-6">

                    </text-field>
                </div>
            </div>

            <div class="card-footer">

                <button class="btn btn-primary" @click="onSubmit"><i
                    :class="iconClass"></i>&nbsp;&nbsp;{{lang(btnName)}}</button>
            </div>
        </div>
    </div>
</template>

<script>

import axios from 'axios'

import { successHandler, errorHandler } from '../../helpers/responseHandler';

import { getIdFromUrl } from '../../helpers/extraLogics';

import TextField from "../../components/Reusable/FormField/TextField.vue";

export default {

    name: 'Add-New-WhiteList-IP',

    data() {

        return {

            title: 'add_new_whitelist_ip',

            iconClass: 'fas fa-save',

            btnName: 'save',

            hasDataPopulated: false,

            loading: false,

            apiEndpoint: '',

            ipAddress: null,

            whitelist_host_comments: null,

            whitelist_host_ip: null,

            hostId: null
        }
    },

    beforeMount() {

        const path = window.location.pathname

        this.getValues(path);

    },

    methods: {

        onChange(value, name) {
            const propertyMap = {
                'whitelist_host_ip': 'whitelist_host_ip',
                'whitelist_host_comments': 'whitelist_host_comments',
            };
            const propertyName = propertyMap[name];
            if (propertyName) {
                this[propertyName] = value;
            }
        },

        getValues(path) {
            const hostId = getIdFromUrl(path);

            if (path.includes('edit')) {
                this.title = 'edit_whitelist';
                this.iconClass = 'fas fa-sync';
                this.btnName = 'update';
                this.hasDataPopulated = false;
                this.getInitialValues(hostId);
                this.hostId = hostId;
            } else {
                this.loading = false;
                this.hasDataPopulated = true;
            }

            this.apiEndpoint = '/api/admin/whitelist/updateOrCreate';
        },


        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/whitelist-edit/' + id).then(res => {

                this.loading = false;

                this.hasDataPopulated = true

                this.updateStatesWithData(res.data.data.host_data);

            }).catch(error => {

                this.loading = false;
            });
        },

        updateStatesWithData(data) {
            if (data.whitelist_host_ip) {
                this.whitelist_host_ip = data.whitelist_host_ip
            }

            if (data.whitelist_host_comments) {
                this.whitelist_host_comments = data.whitelist_host_comments
            }

        },

        onSubmit() {

                this.loading = true
                const formData = {
                    whitelist_host_ip: this.whitelist_host_ip,
                    whitelist_host_comments: this.whitelist_host_comments,
                    api_key_secret: this.getApiKey,
                }
                if (this.hostId) {

                    formData['id'] = this.hostId;
                }

                axios.post(this.apiEndpoint, formData).then(res => {

                    this.loading = false
                    successHandler(res, 'whitelist')

                    if (!this.hostId) {

                        setTimeout(() => {

                            this.$router.push('/whitelist/list')

                        }, 2000)

                    } else {

                        this.getInitialValues(this.hostId)
                    }

                }).catch(err => {

                    this.loading = false

                    errorHandler(err, 'whitelist')
                });
            }
    },

    components: {

        "text-field": TextField,
    }
}
</script>
