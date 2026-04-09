<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header callbacks">
                <h3 class="card-title ">{{lang('latest_callbacks')}}</h3>

                <div class="card-tools">

                    <button type="button"  :disabled="loading" class="btn btn-tool" data-card-widget="refresh"
                            @click="getData()" v-tooltip="lang('refresh')">

                        <i class="fas fa-sync-alt" :class="loading ? 'fa-spin': ''"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="afl_products">
                <div class="datatable-container">
                    <v-client-table
                        v-if="data"
                        :columns="columns"
                        :data="data"
                        :options="options"
                        :key="counter"
                    >
                        <template v-slot:callback_status="props">

                            <span :style="{ color: props.row.callback_status ? 'green' : 'red' }">

                            {{ props.row.callback_status ? lang('active') : lang('inactive')}}
                        </span>
                        </template>

                        <template v-slot:callback_domain="props">

                            <a v-if="props.row.callback_domain" :href="'https://'+props.row.callback_domain" target="_blank">{{props.row.callback_domain}}</a>

                            <span v-else>----</span>

                        </template>
                    </v-client-table>
                </div>
            </div>
        </div>
</template>

<script>

import {lang, formatDateTime} from "../../helpers/extraLogics";
import axios from "axios";

export default {
    name :'latest-callbacks',

    data(){

        return {

            columns:['callback_domain','callback_ip','callback_date_time','callback_status'],

            options : {},

            counter: 0,

            loading : false
        }
    },

    beforeMount(){

        const self =this;

        const date_format = this.generalSetting.date_format.js_format
        const time_format = this.generalSetting.time_format.js_format
        const timezone = this.generalSetting.timezone.name

        this.options ={

            columnsClasses:{

                callback_domain: 'callback_domain',

                callback_date_time: 'callback_date_time',

                callback_ip: 'callback_ip',

                callback_status:   'callback_status',

            },

            templates: {

                callback_date_time(h,row){

                    return formatDateTime(row.callback_date_time, timezone, date_format, time_format)
                },

                callback_ip(h,row){
                    return row.callback_ip ?row.callback_ip : '----';
                },
            },

            headings: {

                callback_domain: this.lang('domain'),

                callback_date_time: this.lang('date'),

                callback_ip: this.lang('ip'),

                callback_status: this.lang('status')

            },
        }
    },

    methods:{
        lang: lang,

        getData() {

            this.loading = true; // Set loading state to true before making the request

            axios
                .get('/api/admin/dashboarddropdown')
                .then((res) => {
                    this.loading = false; // Set loading state to false after the request is completed
                    const { data } = res.data;

                    if (data) {

                        this.$emit('latestCallbacks', 'latestCallbacks', data)
                    }
                })
                .catch((error) => {

                    this.loading = false; // Set loading state to false if an error occurs
                });
        },
    },

    props : {

        data : {type : Array, default : ()=>{}},

        generalSetting : {type : Object, default : () => {}},
    }

};

</script>
