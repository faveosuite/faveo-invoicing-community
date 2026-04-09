<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header versions">
                <h3 class="card-title">{{lang('expiring_version')}}</h3>

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
                        <template v-slot:version_status="props">

                            <span :style="{ color: props.row.version_status ? 'green' : 'red' }">

                            {{ props.row.version_status ? lang('active') : lang('inactive')}}
                        </span>
                        </template>

                        <template v-slot:version_number="props">

                            <router-link v-if="props.row.version_number" :to="'/versions/'+props.row.version_id+'/view'">{{ props.row.version_number }}</router-link>
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
    name :'expiring-version',

    data(){

        return {

            columns:['version_number','version_date','version_expire_date','version_status'],

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

                version_number: 'version_number',

                version_date: 'version_date',

                version_expire_date: 'version_expire_date',

                version_status: 'version_status'
            },

            templates: {

                version_date(h,row){

                    return formatDateTime(row.version_date, timezone, date_format, time_format)
                },

                version_expire_date(h,row){

                    return formatDateTime(row.version_expire_date, timezone, date_format, time_format)
                },
            },

            headings: {

                version_number: this.lang('version'),

                version_date: this.lang('version_date'),

                version_expire_date: this.lang('version_expire_date'),

                version_status: this.lang('status')
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

                        this.$emit('expiredVersions', 'expiredVersions', data)
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
<style>
#afl_products .VueTables__limit {
display :none;
}
</style>

