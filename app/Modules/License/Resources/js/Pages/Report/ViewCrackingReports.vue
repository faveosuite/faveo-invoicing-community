<template>

    <div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light " id="my_crackingreports">

            <div class="card-header">

                <h3 class="card-title">{{lang('view_cracking_reports')}}</h3>

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

    methods: {
        lang
    },

    data() {

        return {

            loading : false,

            data: '',

            columns: ['report_text','license_code','report_date_time','report_status'],

            options: {},

            counter: 0,

            endPoint : '/api/admin/reportCracking?page=1'
        }
    },

    props : {
        generalSetting : {type : Object, default : () => {}},
    },

    beforeMount() {

        const self = this;

        const date_format = this.generalSetting.date_format.js_format
        const time_format = this.generalSetting.time_format.js_format
        const timezone = this.generalSetting.timezone.name

        this.options = {

            sortIcon: {

                base: 'glyphicon',

                up: 'glyphicon-chevron-down',

                down: 'glyphicon-chevron-up'
            },

            texts: { filter: '', limit: '' },

            sortable:  ['report_text', 'license_code', 'report_date_time', 'report_status'],

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

                        data.keyVal = 'report_id';

                        data.idVal = data.report_id;

                        return data;
                    }),

                    count: data.data.total
                }
            },

            columnsClasses: {

                license_code: 'license_code',

                report_date_time: 'report_date_time',

                report_status: 'report_status',

                report_text:  'report_text',
            },

            templates: {

                license_date(h, row) {

                    return formatDateTime(row.license_date, timezone, date_format, time_format)
                },

                latest_callback_date_time(h, row){

                    return formatDateTime(row.latest_callback_date_time, timezone, date_format, time_format)
                },

                report_date_time(h, row) {

                    return formatDateTime(row.report_date_time, timezone, date_format, time_format)
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

                report_status: (f, row) => {

                    return h('span', {
                        'class': row.report_status ? 'text-success' : 'text-danger'
                    }, row.report_status ? this.lang('success'): this.lang('error'))
                },
            },

            pagination: { show : false },

            headings: {

                report_status:   this.lang('status'),

                license_code: this.lang('license_code'),

                report_text:  this.lang('report'),

                report_date_time: this.lang('report_date_time'),

            },
        }
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

#my_crackingreports .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}
.VueTables .table-responsive>table th {
    white-space: nowrap;
    width: 200px;
}

#my_crackingreports .VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}

#my_crackingreports .glyphicon-sort {
     margin-left: 0px;
   margin-top: 0px;
}
</style>


