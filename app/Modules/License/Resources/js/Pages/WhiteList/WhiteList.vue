<template>

    <div class="col-sm-12">


        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light ">

            <div class="card-header">

                <h3 class="card-title">{{lang('view_whitelist_ip')}}</h3>

                <div class="card-tools">

                    <router-link to="/whitelist/create" class="btn btn-tool" v-tooltip="lang('create_whitelist_ip')">

                        <i class="fas fas fa-plus"></i>
                    </router-link>
                </div>
            </div>

            <div class="card-body" id="white_list">

                <data-table :url="endPoint" :show_pagination="true" :dataColumns="columns" alertComponentName="dataTableModal" :option="options" scroll_to="products-list">

                </data-table>
            </div>
        </div>
    </div>
</template>

<script>

import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import {formatDateTime, lang} from "../../helpers/extraLogics";

export default {

    name: 'whitelist',
    methods: {lang},

    props : {
    },

    data() {

        return {

            loading: false,

            data: '',

            columns: ['whitelist_host_ip', 'whitelist_host_comments', 'whitelist_host_date','actions'],

            options: {},

            counter: 0,

            endPoint : '/api/admin/view-Whitelist?page=1'
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

            sortable:  ['whitelist_host_comments', 'whitelist_host_date'],

            filterable : [ 'whitelist_host_ip' ],

            columnsClasses: {

                whitelist_host_ip: 'whitelist_host_ip',

                whitelist_host_comments: 'whitelist_host_comments',

                whitelist_host_date: 'whitelist_host_date',
            },

            pagination: { show : false },

            requestAdapter(data) {

                return {

                    'sort_field' : data.orderBy ? data.orderBy : 'id',

                    'sort_order' : data.ascending ? 'desc' : 'asc',

                    'search_query' : data.query,

                    perPage : data.limit,
                }
            },

            responseAdapter({data}) {

                return {

                    data: data.data.data.map(data => {

                        data.edit_url = '/whitelist/' + data.id  + '/edit';

                        data.delete_url = '/api/admin/delete-whitelist-ip';

                        data.keyVal = 'id';

                        data.idVal = data.id;

                        return data;
                    }),

                    count: data.data.total
                }
            },

            templates: {

                whitelist_host_ip(h, row) {

                    return row.whitelist_host_ip ? row.whitelist_host_ip : '---';
                },

                whitelist_host_comments(h, row) {

                    return row.whitelist_host_comments ? row.whitelist_host_comments : '---'
                },

                whitelist_host_date(h, row) {

                    return row.whitelist_host_date
                },
            },

            headings: {

                whitelist_host_ip: 'IP Address',

                whitelist_host_comments: 'Comments',

                whitelist_host_date: 'Date',

                actions: 'Actions'
            },
        }
    },

    components: {
        'data-table': DynamicDataTable
    }
};
</script>

<style>
 .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}

.VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}
</style>
