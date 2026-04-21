<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header callbacks">
                <h3 class="card-title ">{{lang('latest_clients')}}</h3>

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
                        <template v-slot:client_status="props">

                            <span :style="{ color: props.row.client_status ? 'green' : 'red' }">

                            {{ props.row.client_status ? lang('active') : lang('inactive')}}
                        </span>
                        </template>

                        <template v-slot:client_email="props">

                            <a v-if="props.row.client_email" :href="basePath() + '/clients/' + props.row.client_id">{{ props.row.client_email }}</a>

                            <span v-else>----</span>
                        </template>

                        <template v-slot:full_name="props">

                            <a v-if="props.row.full_name" :href="basePath() + '/clients/' + props.row.client_id">{{ props.row.full_name }}</a>

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
    name :'latest-clients',

    data(){

        return {

            columns:['full_name','client_email','client_active_date','license_count', 'client_status'],

            options : {},

            counter: 0,

            loading : false
        }
    },

    beforeMount(){

        const self =this;


        this.options ={

            columnsClasses:{

                full_name: 'full_name',

                client_email: 'client_email',

                client_active_date: 'client_active_date',

                license_count: 'license_count',

                client_status:   'client_status',

            },

            templates: {

                client_active_date(h,row){

                    return row.client_active_date
                },

            },

            headings: {

                full_name: this.lang('name'),

                client_email: this.lang('email'),

                client_active_date: this.lang('created_at'),

                license_count: this.lang('licenses'),

                client_status: this.lang('status')

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

                        this.$emit('latestClients', 'latestClients', data)
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
