<template>

    <div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light " id="my_systemreports">

            <div class="card-header">

                <h3 class="card-title">{{lang('view_system_reports')}}</h3>

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
import moment from "moment";
import {h} from "vue";
import {RouterLink} from "vue-router";
export default {

    name: 'licenses-list',

    methods: {
        lang
    },

    props : {
    },

    data() {

        return {

            loading : false,

            data: '',

            columns: ['report_text' ,'user_formatted','report_date_time','report_status'],

            options: {},

            counter: 0,

            endPoint : '/api/admin/reportSystem?page=1'
        }
    },

    beforeMount() {

        const self = this;


        this.options = {

            sortIcon: {

                base: 'glyphicon',

                up: 'glyphicon-chevron-down',

                down: 'glyphicon-chevron-up'
            },

            texts: { filter: '', limit: '' },

            sortable:  ['report_text', 'user_formatted', 'report_date_time', 'report_status'],

            filterable : [ 'report_text' ],

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

                report_date_time: 'report_date_time',

                report_text:  'report_text',

                report_status:  'status',

                user_formatted:  'format'
            },

            templates: {

                license_code(h, row) {

                    return row.license_code ? row.license_code : '---';
                },

                report_date_time(h, row) {

                    return row.report_date_time
                },

                license_date(h, row) {

                    return row.license_date
                },

                latest_callback_date_time(h, row) {

                    return row.latest_callback_date_time
                },

                user_formatted: (f, row) => {

                    if(row.user_formatted && row.user_formatted !== 'System') {

                        return h(RouterLink, {

                            to: '/clients/' + row.account_id + '/view'

                        },[row.user_formatted])

                    } else if(row.user_formatted && row.user_formatted === 'System') {

                        return row.user_formatted
                    } else {

                        return '----'
                    }
                },

                report_status: (f, row) => {

                    return h('span', {
                        'class': row.report_status ? 'text-success' : 'text-danger'
                    }, row.report_status ? this.lang('success'): this.lang('error'))
                },

            },

            pagination: { show : false },

            headings: {

                report_text:  this.lang('report'),

                report_date_time: this.lang('report_date_time'),

                report_status:  this.lang('status'),

                user_formatted:  this.lang('user'),

            },
        }
    },

    components : {

        'data-table' : DynamicDataTable
    }
};
</script>

<style scoped>
.report_date_time,
.report_text,
.status,
.format{
    max-width: 200px;
    word-break: break-all;
}

#my_systemreports .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}
.VueTables .table-responsive>table th {
    white-space: nowrap;
    width: 200px;
}

#my_systemreports .VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}
#my_systemreports .glyphicon-sort {
    margin-left: 0px;
    margin-top: 0px;
}
</style>


