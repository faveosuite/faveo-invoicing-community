<template>

    <div class="container-fluid">

        <div class="col-sm-12">

            <div class="alert alert-info">

                <span>{{lang('callbacks_description')}}</span>
            </div>

            <div class="row" v-if="loading">

                <custom-loader :duration="4000"></custom-loader>
            </div>

            <alert componentName="product" />

            <div class="card">

                <div class="card-header data-table-header border-0 p-0 pt-1">

                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">

                        <li class="nav-item" @click="updateData('license')" >

                            <span class="nav-link card-header-link" :class="{ active: activeTab === 'license' }" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" aria-controls="custom-tabs-one-home">{{lang('license_callbacks')}}</span>

                        </li>
                        <li class="nav-item" @click="updateData('update')" >
                            <span class="nav-link card-header-link" :class="{ active: activeTab === 'update' }" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" aria-controls="custom-tabs-one-home">{{lang('update_callbacks')}}</span>
                        </li>
                    </ul>
                </div>

                <div class="card-body">

                    <data-table v-if="!loading" :url="endPoint" ref="dataTable" :show_pagination="true" :dataColumns="columns" :option="options" scroll_to="licenses-list">

                    </data-table>
                </div>

            </div>

        </div>

    </div>

</template>

<script>

import {formatDateTime, lang} from "../../helpers/extraLogics";
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import {h} from "vue";
import {RouterLink} from "vue-router";

    export default {

        name: "callbacks",

        data() {

            return {

                endPoint : '',

                showModal : false,

                hasDataPopulated: false,

                columns: [],

                counter : 0,

                loading: true,

                activeTab: 'license',

            }
    },

        components: {

            'data-table': DynamicDataTable
        },

        beforeMount() {

            this.updateData('license')
        },

        props : {

            generalSetting : {type : Object, default : () => {}},
        },

        methods: {
            lang,

            updateData(value) {

                this.activeTab = value; // Set the active tab
                const date_format = this.generalSetting.date_format.js_format
                const time_format = this.generalSetting.time_format.js_format
                const timezone = this.generalSetting.timezone.name

                if(value === 'license') {

                    this.loading = true

                    this.endPoint = '/api/admin/showLicenseCallbacks'

                    this.columns = ['product_title', 'license', 'callback_ip', 'callback_domain', 'callback_date_time', 'callback_status']

                    this.options = {

                        sortIcon: {

                            base: 'glyphicon',

                            up: 'glyphicon-chevron-down',

                            down: 'glyphicon-chevron-up'
                        },

                        texts: { filter: '', limit: '' },

                        sortable:  ['product_title', 'callback_date_time', 'callback_status'],

                        filterable : [ 'license' ],

                        requestAdapter(data) {

                            return {

                                'sort_field' : data.orderBy ? data.orderBy : 'callback_id',

                                'sort_order' : data.ascending ? 'desc' : 'asc',

                                'search_query' : data.query.trim(),

                                perPage : data.limit,
                            }
                        },

                        responseAdapter({data}) {

                            return {

                                data: data.data.data.map(data => {

                                    data.keyVal = 'callback_id';

                                    data.idVal = data.callback_id;

                                    return data;
                                }),

                                count: data.data.total
                            }
                        },

                        columnsClasses: {

                            license: 'license',

                            product: 'product_title',

                            callback_ip: 'callback_ip',

                            callback_domain: 'callback_domain',

                            callback_date_time: 'callback_date_time',

                            callback_status: 'callback_status',
                        },

                        templates: {

                            callback_date_time(h, row) {

                                return formatDateTime(row.callback_date_time, timezone, date_format, time_format)
                            },

                            product_title: (f, row) => {

                                if(row.product && row.product.product_title && row.product.product_id) {

                                    return h(RouterLink, {

                                        to: '/products/' + row.product.product_id + '/view'

                                    },[row.product.product_title])

                                } else {
                                    return '----'
                                }
                            },

                            callback_status: (f, row) => {

                                return h('span', {
                                    'class': row.callback_status ? 'text-success' : 'text-danger'
                                }, row.callback_status ? this.lang('active'): this.lang('inactive'))
                            },

                            callback_domain: (f, row) => {

                                if(row.callback_domain) {

                                    return h('a', {

                                        href: 'https://'+row.callback_domain,
                                        target: '_blank'

                                    },[row.callback_domain])

                                } else {
                                    return '----'
                                }
                            },

                            license: (f, row) => {

                                if(row.license && row.license.license_code && row.license.license_id) {

                                    return h(RouterLink, {

                                        to: '/licenses/' + row.license.license_id + '/view'

                                    },[row.license.license_code.match(/.{1,4}/g).join('-')])

                                } else {
                                    return '----'
                                }
                            },
                        },

                        pagination: { show : false },

                        headings: {

                            license: this.lang('license_code'),

                            product_title: this.lang('product'),

                            callback_ip: this.lang('ip_address'),

                            callback_domain: this.lang('domain'),

                            callback_date_time: this.lang('date'),

                            callback_status: this.lang('status')
                        },
                    }

                    this.loading = false

                } else {

                    this.loading = true

                    this.endPoint = '/api/admin/showUpdateCallbacks'

                    this.columns = ['product_title', 'version', 'callback_ip', 'callback_types', 'callback_date_time', 'callback_status']

                    this.options = {

                        sortIcon: {

                            base: 'glyphicon',

                            up: 'glyphicon-chevron-down',

                            down: 'glyphicon-chevron-up'
                        },

                        texts: { filter: '', limit: '' },

                        sortable:  ['product_title', 'callback_types', 'callback_date_time', 'callback_status'],

                        filterable:  ['product_title'],

                        requestAdapter(data) {

                            return {

                                'sort_field' : data.orderBy ? data.orderBy : 'callback_id',

                                'sort_order' : data.ascending ? 'desc' : 'asc',

                                'search_query' : data.query.trim(),

                                perPage : data.limit,
                            }
                        },

                        responseAdapter({data}) {

                            return {

                                data: data.data.data.map(data => {

                                    data.keyVal = 'callback_id';

                                    data.idVal = data.callback_id;

                                    return data;
                                }),
                                count: data.data.total
                            }
                        },

                        columnsClasses: {

                            product_title: 'product_title',

                            version: 'version_number',

                            callback_ip: 'callback_ip',

                            callback_types : 'callback_types',

                            callback_date_time: 'callback_date_time',

                            callback_status: 'callback_status',

                        },

                        templates: {

                            callback_date_time(h, row) {

                                return formatDateTime(row.callback_date_time, timezone, date_format, time_format)
                            },

                            product_title: (f, row) => {

                                if(row.product && row.product.product_title && row.product.product_id) {

                                    return h(RouterLink, {

                                        to: '/products/' + row.product.product_id + '/view'

                                    },[row.product.product_title])

                                } else {
                                    return '----'
                                }
                            },

                            version: (f, row) => {

                                if(row.version && row.version.version_number && row.version.version_id) {

                                    return h(RouterLink, {

                                        to: '/versions/' + row.version.version_id + '/view'

                                    },[row.version.version_number])

                                } else {
                                    return '----'
                                }
                            },

                            callback_status: (f, row) => {

                                return h('span', {
                                    'class': row.callback_status ? 'text-success' : 'text-success'
                                }, row.callback_status ? this.lang('active'): this.lang('inactive'))
                            },

                        },

                        pagination: { show : false },

                        headings: {

                            product_title: this.lang('product'),

                            version: this.lang('version'),

                            callback_ip: this.lang('ip_address'),

                            callback_types: this.lang('types'),

                            callback_date_time : this.lang('date'),

                            callback_status: this.lang('status'),

                        },

                    }

                    this.loading = false

                }
            }

        },
    }

</script>

<style scoped>
.product_title,
.client_email_or_license_code,
.callback_ip,
.callback_domain,
.callback_date_time,
.callback_status,
.version_number,
.callback_types{
    max-width: 200px;
    word-break: break-all;
}

#my_callbacks .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}

#my_callbacks .VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}
.data-table-header {
    background-color: #ebebeb;
}
.card-header-link{
    color: black;
}
.card-header-link:hover:not(.active){
    color: #007bff;
    cursor: pointer;
}
</style>
