<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header versions">
                <h3 class="card-title">{{'Latest Versions'}}</h3>
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

                            {{ props.row.version_status ? 'Active' : 'Inactive'}}
                        </span>
                            </template>

                            <template v-slot:version_number="props">

                                <router-link :to="'/versions/'+props.row.version_id+'/view'">{{ props.row.version_number }}</router-link>
                            </template>

                            <template v-slot:product="props">
                                <router-link v-if="props.row.product && Array.isArray(props.row.product) && props.row.product[0] && props.row.product[0].product_title" :to="'/products/'+props.row.product[0].product_id+'/view'">{{ props.row.product[0].product_title }}</router-link>
                                <span v-else>----</span>
                            </template>
                        </v-client-table>
                    </div>
                </div>
            </div>
</template>

<script>

import moment from "moment";
import {formatDateTime, lang} from "../../helpers/extraLogics";
import axios from "axios";

export default {
    name :'latest-version',

    data(){

        return {

            columns:['product', 'version_number','version_date','version_upgrade_count','version_status'],

            options : {},

            counter: 0,

            loading: false

        };
    },

    beforeMount(){

        const self =this;


        this.options ={

            columnsClasses:{

                product: 'product_title',

                version_number: 'version_number',

                version_upgrade_count: 'version_upgrade_count',

                version_date: 'version_date',

                version_status:    'version_status',
            },

            templates: {

                version_date(h,row){

                    return row.version_date
                },

                version_expire_date(h,row){

                    return row.version_expire_date
                },

            },

            headings: {

                product: 'Product',

                version_date: 'Released Date',

                version_upgrade_count: 'Upgrades',

                version_number: "Version",

                version_status: "Status"

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

                        this.$emit('latestVersions', 'latestVersions', data)
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
<style>

 .datatable-container {
     max-height: 250px; /* Adjust the maximum height as per your needs */
     overflow-y: auto;
 }
.VueTables .table-responsive {
    display: block;
    width: 100%;
    position: inherit;
    overflow-x :hidden;

}
</style>

