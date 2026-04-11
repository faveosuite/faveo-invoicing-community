<template>

    <div class="row" v-if="loading">

        <loader :duration="4000"></loader>
    </div>

        <div class="card card-light">

            <div class="card-header licences">

                <h3 class="card-title">{{'Latest Installations'}}</h3>

                <div class="card-tools">

                    <button type="button"  :disabled="loading" class="btn btn-tool" data-card-widget="refresh"
                            @click="getData()" v-tooltip="lang('refresh')">

                        <i class="fas fa-sync-alt" :class="loading ? 'fa-spin': ''"></i>
                    </button>
                </div>
            </div>
            <div class="card-body" id="afl_products">
                <div class="datatable-container my-table-container">
                    <v-client-table
                        v-if="data"
                        :columns="columns"
                        :data="data"
                        :options="options"
                        :key="counter"
                    >
                        <template v-slot:product_status="props">

                        <span :class="props.row.product_status ? 'btn btn-success btn-xs' : 'btn btn-secondary btn-xs'">

                            {{ props.row.product_status ? 'Active' : 'Inactive'}}
                        </span>
                        </template>

                        <template v-slot:installation_domain="props">

                            <a v-if="props.row.installation_domain" :href="'https://'+props.row.installation_domain" target="_blank">{{props.row.installation_domain}}</a>

                            <span v-else>----</span>

                        </template>

                        <template v-slot:license="props">

                            <router-link v-if="props.row.license && props.row.license.license_id" :to="'/licenses/' + props.row.license.license_id + '/view'">{{ props.row.license.license_code.match(/.{1,4}/g).join('-')}}</router-link>

                            <span v-else>----</span>
                        </template>

                        <template v-slot:installation_status="props">

                            <span :style="{ color: props.row.installation_status ? 'green' : 'red' }">

                            {{ props.row.installation_status ? 'Active' : 'Inactive'}}
                        </span>
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
    name: 'latest-installations',

    data() {
        return {
            columns: ['license','installation_ip','installation_date','installation_domain', 'installation_status'],
            options: {},
            counter: 0,
            loading: false
        };
    },

    beforeMount() {

        const self = this;


        this.options = {

            columnsClasses: {

                license: 'license_code',

                installation_ip: 'installation_ip',

                installation_date: 'installation_date',

                installation_domain: 'installation_domain',

                installation_status: 'installation_status'
            },

            templates: {

                installation_ip(h, row) {
                    return row.installation_ip ? row.installation_ip : '----';
                },

                installation_date(h, row) {

                    return row.installation_date
                },
            },

            headings: {

                license: 'License Code',

                installation_date: 'Installation Date',

                installation_ip: 'IP',

                installation_domain: 'Domain',

                installation_status: 'Status'
            },
        };
    },

    methods: {

        lang: lang,

        getData() {

            this.loading = true; // Set loading state to true before making the request

            axios
                .get('/api/admin/dashboarddropdown')
                .then((res) => {
                    this.loading = false; // Set loading state to false after the request is completed
                    const { data } = res.data;

                    if (data) {

                        this.$emit('latestInstallations', 'latestInstallations', data)
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
.my-table-container {
    max-height: 300px; /* Adjust the maximum height as per your needs */
    overflow-y: auto !important;
}
.VueTables .table-responsive>table th {
    white-space: nowrap;
    width: 100px;
}
</style>

