<template>

    <div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light ">

            <div class="card-header">

                <h3 class="card-title">{{lang('all_licenses')}}</h3>

                <div class="card-tools">

                    <router-link to="/licenses/create" class="btn btn-tool" v-tooltip="lang('create_license')">

                        <i class="fas fas fa-plus"></i>
                    </router-link>
                </div>
            </div>

            <div class="card-body" id="my_licenses">

                <data-table v-if="!loading" :url="endPoint" :show_pagination="true" :showColumn="true" :dataColumns="selectedColumns" :allColumns="allColumns" @columns="updateColumn" :option="options" scroll_to="licenses-list">

                </data-table>

            </div>

        </div>
    </div>
</template>

<script>

import {lang, formatDateTime} from "../../helpers/extraLogics";
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import {useStore} from 'vuex';
import {computed, h} from "vue";
import {errorHandler, successHandler} from "../../helpers/responseHandler";
import {RouterLink} from "vue-router";

export default {

    name: 'licenses-list',

    props : {
        generalSetting : {type : Object, default : () => {}},
    },

    setup() {

        const store = useStore();

        return {

            formattedTime : computed(()=>store.getters.formattedTime)
        }
    },

    data() {

        return {

            loading: false,

            data: '',

            selectedColumns: [],

            allColumns: ['license_code','client_email', 'product_title', 'license_order_number', 'license_domain', 'license_ip','license_date', 'installation_counts', 'call_backs_count',
                'latest_call_backs', 'license_limit', 'license_expire_date','license_updates_date' ,'license_support_date', 'license_status'],

            options: {},

            counter: 0,

            endPoint : '/api/admin/viewLicenses?page=1',
        }
    },

    beforeMount() {

        const self = this;

        const date_format = this.generalSetting.date_format.js_format
        const time_format = this.generalSetting.time_format.js_format
        const timezone = this.generalSetting.timezone.name

        this.getColumns();

        this.options = {

            sortIcon: {

                base: 'glyphicon',

                up: 'glyphicon-chevron-down',

                down: 'glyphicon-chevron-up'
            },

            texts: { filter: '', limit: '' },

            sortable:  ['product_title', 'client_email', 'license_code', 'license_limit', 'license_order_number','license_expire_date','license_support_date','license_updates_date', 'license_status'],

            filterable:  ['product_title'],

            requestAdapter(data) {

                return {

                    'sort_field' : data.orderBy ? data.orderBy : 'license_id',

                    'sort_order' : data.ascending ? 'desc' : 'asc',

                    'search_query' : data.query.trim(),

                     perPage : data.limit,
                }
            },

            responseAdapter({data}) {

                return {

                    data: data.data.data.map(data => {

                        data.edit_url = '/licenses/' + data.license_id + '/edit';

                        data.delete_url = '/api/admin/license/delete';

                        data.view_url = '/licenses/' + data.license_id + '/view';

                        data.keyVal = 'license_id';

                        data.idVal = data.license_id;

                        return data;
                    }),
                    count: data.data.total
                }
            },

            columnsClasses: {

                product_title: 'product_title',

                license_ip: 'license_ip',

                license_domain: 'license_domain',

                license_code: 'license_code',

                client_email: 'client_email',

                license_order_number : 'license_order_number',

                installation_counts: 'installation_counts',

                call_backs_count: 'license_callbacks',

                latest_call_backs: 'latest_call_backs',

                license_limit: 'license_limit',

                license_expire_date: 'license_expire_date',

                license_updates_date: 'license_updates_date',

                license_support_date: 'license_support_date',

                license_status: 'license_status',

                actions:      'actions',
            },

            templates: {

                license_ip(h, row) {

                    return row.license_ip ? row.license_ip : '----';
                },

                license_updates_date(h, row) {

                    return formatDateTime(row.license_updates_date, timezone, date_format, time_format)
                },

                latest_call_backs(h, row) {

                    return formatDateTime(row.latest_call_backs, timezone, date_format, time_format)
                },

                license_support_date(h, row) {

                    return formatDateTime(row.license_support_date, timezone, date_format, time_format)
                },

                license_date(h, row) {

                    return formatDateTime(row.license_date, timezone, date_format, time_format)
                },

                license_expire_date(h, row) {

                    return formatDateTime(row.license_expire_date, timezone, date_format, time_format)
                },

                license_code: (f, row) => {

                    if(row.license_code && row.license_id) {

                        return h(RouterLink, {

                            to: '/licenses/' + row.license_id + '/view'

                        },[row.license_code.match(/.{1,4}/g).join('-')])

                    } else {
                        return '----'
                    }
                },

                product_title: (f, row) => {

                    if(row.product && row.product.product_title && row.product.product_id) {

                        return h(RouterLink, {

                            to: '/products/' + row.product_id + '/view'

                        },[row.product_title])

                    } else {
                        return '----'
                    }
                },

                client_email: (f, row) => {

                    if(row.client_email) {

                        return h(RouterLink, {

                            to: '/clients/' + row.client_id + '/view'

                        },[row.client_email])

                    } else {
                        return '----'
                    }
                },

                license_domain: (f, row) => {

                    if(row.license_domain) {

                        return h('a', {

                            href: 'https://'+row.license_domain,
                            target: '_blank'

                        },[row.license_domain])

                    } else {
                        return '----'
                    }
                },

                license_status: (f, row) => {

                    return h('span', {
                        'class': row.license_status ? 'text-success' : 'text-danger'
                    }, row.license_status ? this.lang('active'): this.lang('inactive'))
                },

                license_order_number: (f, row) => {

                    if(row.license_order_url && row.license_order_url.includes('href')) {

                        return h('a', {

                            href: this.extractHref(row.license_order_url),
                            target: '_blank'
                        },[row.license_order_number])

                    } else if(row.license_order_number) {

                        return row.license_order_number

                    } else {

                        return  '----'
                    }

                }
            },

            pagination: { show : false },

            headings: {

                product_title: this.lang('product'),

                license_ip: this.lang('license_ip'),

                license_domain: this.lang('license_domain'),

                client_email: this.lang('email'),

                license_code: this.lang('license_code'),

                license_order_number : this.lang('order_number'),

                installation_counts: this.lang('installations_count'),

                call_backs_count: this.lang('callbacks_count'),

                latest_call_backs: this.lang('latest_callbacks'),

                license_limit: this.lang('license_limit'),

                license_expire_date: this.lang('license_expiry'),

                license_updates_date: this.lang('updates_expiry'),

                license_support_date: this.lang('support_expiry'),

                license_status: this.lang('status'),

                actions: this.lang('actions')
            },
        }
    },

    methods : {

        lang,

        getColumns() {

            this.loading = true;

            axios.get('/api/admin/getLicenseColumn').then(res => {

                this.allColumns.map(col => {

                    if (res.data.data.includes(col)) {

                        this.selectedColumns.push(col)
                    }
                })

                this.selectedColumns.push('actions')

            }).catch(err => {

                this.loading = false;
            })

            this.loading = false;
        },

        updateColumn(value) {

            this.loading = true

            const payload  = {
                "selected_columns": value
            }
            axios.post('/api/admin/saveLicenseColumn', payload).then(res => {

                this.selectedColumns = [];

                successHandler(res, 'dataTableModal')

                setTimeout(()=>{

                    this.getColumns()

                    this.loading = false;
                },100)

            }).catch(err => errorHandler(err, 'dataTableModal'))
        },

        extractHref(orderUrl) {

            const parser = new DOMParser();

            // Parse the HTML string

            const parsedHtml = parser.parseFromString(orderUrl, 'text/html');

            // Get the root element of the parsed HTML

            const htmlElement = parsedHtml.documentElement;

            return htmlElement.querySelector('#href_link') ?? ''
        },
    },

    components : {

        'data-table' : DynamicDataTable
    }
};
</script>

<style>

.license_product_title,
.license_code,
.license_install,
.license_callbacks,
.latest_callback_time,
.license_date {
    max-width: 200px;
    word-break: break-all;
}

#my_licenses .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}

.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

#my_licenses .VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}
</style>
