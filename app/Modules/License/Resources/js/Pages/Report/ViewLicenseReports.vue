<template>

    <div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light" id="my_licensereports">

            <div class="card-header">

                <h3 class="card-title">{{ lang('view_license_reports') }}</h3>
            </div>

            <div class="card-body" id="my_licenses">

                <data-table :url="endPoint" :show_pagination="true" :dataColumns="columns" :option="options" scroll_to="licenses-list">

                </data-table>
            </div>
        </div>
    </div>
</template>

<script>

import {formatDateTime, lang} from '../../helpers/extraLogics'
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import {h} from "vue";
import {RouterLink} from "vue-router";

export default {

    name: 'licenses-list',

    methods : {
        lang
    },

    props : {
    },

    data() {

        return {

            data: '',

            columns: ['report_text', 'user' ,'license','report_date_time', 'report_status'],

            options: {},

            counter: 0,

            loading: false, // Add the 'loading' property

            endPoint : '/api/admin/reportLicense?page=1'
        };
    },

    created() {

        this.emitter.on('refreshData', this.updateData);
    },

    beforeMount() {


        this.options = {

            sortIcon: {

                base: 'glyphicon',

                up: 'glyphicon-chevron-down',

                down: 'glyphicon-chevron-up',
            },

            texts: { filter: '', limit: '' },

            sortable:  ['product_title', 'report_text', 'report_date_time', 'report_status'],

            filterable : [ 'product_title', 'report_text' ],

            requestAdapter(data) {

                return {

                    'sort_field' : data.orderBy ? data.orderBy : 'report_date_time',

                    'sort_order' : data.ascending ? 'desc' : 'asc',

                    'search_query' : data.query.trim(),

                     perPage : data.limit,
                }
            },

            responseAdapter({data}) {

                return {

                    data: data.data.data.map(data => {

                        data.keyVal = 'id';

                        data.idVal = data.id;

                        return data;
                    }),

                    count: data.data.total
                }
            },

            columnsClasses: {

                product_title: 'license_product_title',

                license: 'license_code',

                user: 'client_email',

                report_date_time: 'report_date_time',

                report_text: 'report_text',

                report_status: 'status',
            },

            pagination: { show : false },

            headings: {

                license: this.lang('license_code'),

                user: this.lang('email'),

                report_text: this.lang('report'),

                report_date_time: this.lang('report_date_time'),

                report_status: this.lang('status'),
            },

            templates :{

                user(f, row) {

                    if(row.client_email) {

                        return h(RouterLink, {

                            to: '/clients/' + row.client_id + '/view'

                        },[row.client_email])

                    } else {

                        return '----'
                    }
                },


                report_date_time(h, row) {

                    return row.report_date_time

                },

                license: (f, row) => {

                    if(row.license_code && row.license_id) {

                        return h(RouterLink, {

                            to: '/licenses/' + row.license_id + '/view'

                        },[row.license_code.match(/.{1,4}/g).join('-')])

                    } else {
                        return '----'
                    }
                },

                report_status: (f, row) => {

                    return h('span', {
                        'class': row.report_status ? 'text-success' : 'text-danger'
                    }, row.report_status ? this.lang('success'): this.lang('error'))
                },
            }
        };
    },

    components : {

        'data-table' : DynamicDataTable
    }

};
</script>

<style scoped>
.license_product_title,
.license_code,
.report_date_time,
.report_text,
.status {
    max-width: 200px;
    word-break: break-all;
}

.VueTables .table-responsive>table th {
    white-space: nowrap;
    width: 200px;
}
#my_licensereports .glyphicon-sort {
    margin-left: 0px;
    margin-top: 0px;
}
</style>
