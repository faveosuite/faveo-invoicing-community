<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">
            <div class="card-header">
                <h3 class="card-title">{{ 'Latest Products' }}</h3>

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
                        <template v-slot:product_status="props">

                            <span :style="{ color: props.row.product_status ? 'green' : 'red' }">

                            {{ props.row.product_status ? 'Active' : 'Inactive'}}
                        </span>
                        </template>

                        <template v-slot:product_title="props">

                            <router-link :to="'/products/'+props.row.product_id+'/view'">{{ props.row.product_title }}</router-link>
                        </template>

                        <template v-slot:versions="props">
                            <router-link v-if="props.row.versions && props.row.versions.version_number" :to="'/versions/'+props.row.versions.version_id+'/view'">{{ props.row.versions.version_number }}</router-link>
                            <span v-else>----</span>
                        </template>

                    </v-client-table>
                </div>
            </div>
        </div>
</template>

<script>

import {formatDateTime, lang} from "../../helpers/extraLogics";
import moment from "moment";
import 'moment-timezone'
import axios from "axios";

export default {
    name :'latest-product',

    data(){

        return {

            columns:['product_title','product_sku','versions', 'installations_count','licenses_count' ,'product_status'],

            options : {},

            counter: 0,

            loading: false
        };
    },

    beforeMount(){

        const self =this;

        const date_format = this.generalSetting.date_format.js_format;
        const time_format = this.generalSetting.time_format.js_format;
        const timezone = this.generalSetting.timezone.name;

        this.options ={

            columnsClasses:{

                product_title: 'product_title',

                product_sku:   'product_sku',

                product_date: 'product_date',

                product_status: 'product_status',
            },

            templates: {

                product_date(h, row) {

                    return formatDateTime(row.product_date, timezone, date_format, time_format)
                }
            },

            headings: {

                product_title: 'Product',

                product_sku: 'SKU',

                versions: 'Versions',

                licenses_count: 'Licenses',

                installations_count: 'Installations',

                product_status: 'Status'
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

                        this.$emit('latestProducts', 'latestProducts', data)
                    }
                })
                .catch((error) => {

                    this.loading = false; // Set loading state to false if an error occurs
                });
        },
    },

    props : {

        data : {type: Array, default : ()=>{}},

        generalSetting : {type : Object, default : () => {}},
    }
};

</script>
<style>

#afl_products .VueTables__search-field{
    display : none;
}

#afl_products .VuePagination .text-center {
    display : none;
}
#afl_products .datatable-container {
    max-height: 300px; /* Adjust the maximum height as per your needs */
    overflow-y: auto;
    overflow-x: scroll; /* Allow horizontal scrolling */
    scrollbar-width: thin; /* Width of the scrollbar */
    scrollbar-color: transparent transparent;
}
#afl_products  .glyphicon-sort {
    margin-left: 100px;
    visibility: hidden;
    margin-top: -19px;
}
#afl_products .VueTables .table-responsive {
    display: block;
    width: 100%;
    position: inherit;
    overflow-x: visible;
}
/* Style the scrollbar track and thumb */
.datatable-container::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.datatable-container::-webkit-scrollbar-thumb {
    background-color: transparent;
}
.datatable-container::-webkit-scrollbar-track {
    background-color: transparent;
}
</style>
