<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header callbacks">
                <h3 class="card-title ">{{lang('latest_licenses')}}</h3>

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
                        <template v-slot:license_status="props">

                            <span :style="{ color: props.row.license_status ? 'green' : 'red' }">

                            {{ props.row.license_status ? lang('active') : lang('inactive')}}
                        </span>
                        </template>

                        <template v-slot:license_code="props">

                            <router-link v-if="props.row.license_code && props.row.license_id" :to="'/licenses/' + props.row.license_id + '/view'">{{ props.row.license_code.match(/.{1,4}/g).join('-')}}</router-link>

                            <span v-else>----</span>
                        </template>

                        <template v-slot:product="props">

                            <a v-if="props.row.product_title && props.row.product_id" :href="basePath() + '/products/' + props.row.product_id + '/edit'">{{props.row.product_title}}</a>

                            <span v-else>----</span>
                        </template>
                    </v-client-table>
                </div>
            </div>
        </div>
</template>

<script>

import {formatDateTime, lang} from "../../helpers/extraLogics";
import axios from "axios";

export default {
    name :'latest-licenses',

    data(){

        return {

            columns:['license_code','product','license_date','license_status'],

            options : {},

            counter: 0,

            loading : false
        }
    },

    beforeMount(){

        const self =this;


        this.options ={

            columnsClasses:{

                license_code: 'license_code',

                product: 'product_title',

                license_date: 'license_date',

                license_status:   'license_status',

            },

            templates: {

                license_date(h,row){

                    return row.license_date
                },

            },

            headings: {

                license_code: this.lang('license_code'),

                product: this.lang('product'),

                license_date: this.lang('activation_date'),

                license_status: this.lang('status')

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

                        this.$emit('latestLicenses', 'latestLicenses', data)
                    }
                })
                .catch((error) => {

                    this.loading = false; // Set loading state to false if an error occurs
                });
        },
    },

    props : {

        data : {type : Array, default : ()=>{}},

    }

};

</script>
