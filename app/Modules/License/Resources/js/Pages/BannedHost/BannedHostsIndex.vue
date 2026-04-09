<template>

    <div class="col-sm-12">

        <div class="alert alert-info">
            <span>View existing banned hosts. If any banned host needs to be modified, click the IP address. If any banned
                host needs to be deleted, check the IP address and click the 'Submit' button.</span>
        </div>

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light ">

            <div class="card-header">

                <h3 class="card-title">{{lang('view_banned_hosts')}}</h3>

                <div class="card-tools">

                    <router-link to="/banned-hosts/create" class="btn btn-tool" v-tooltip="lang('create_banned_host')">

                        <i class="fas fas fa-plus"></i>
                    </router-link>
                </div>
            </div>

            <div class="card-body" id="banned_hosts">

                <data-table :url="endPoint" :show_pagination="true" alertComponentName="dataTableModal" :dataColumns="columns" :option="options" scroll_to="products-list">

                </data-table>
            </div>
        </div>
    </div>
</template>

<script>


    import {formatDateTime, lang} from '../../helpers/extraLogics'
    import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";

    export default {

        name: 'banned-hosts',

        data() {

            return {

                loading : false,

                data: '',

                columns: ['banned_host_ip', 'banned_host_comments', 'banned_host_date','actions'],

                options: {},

                counter: 0,

                endPoint : '/api/admin/viewBannedHost?page=1'
            }
        },

        methods: {
            lang
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

                sortable:  ['banned_host_ip', 'banned_host_comments', 'banned_host_date', 'banned_host_blocks', 'banned_host_last_block_date'],

                filterable : [ 'banned_host_ip' ],

                requestAdapter(data) {

                    return {

                        'sort_field' : data.orderBy ? data.orderBy : 'banned_host_id',

                        'sort_order' : data.ascending ? 'desc' : 'asc',

                        'search_query' : data.query.trim(),

                         perPage : data.limit,
                    }
                },

                responseAdapter({data}) {

                    return {

                        data: data.data.data.map(data => {

                            data.edit_url = '/banned-hosts/' + data.banned_host_id + '/edit';

                            data.delete_url = '/api/admin/bannedHosts/delete';

                            data.keyVal = 'banned_host_id';

                            data.idVal = data.banned_host_id;

                            return data;
                        }),

                        count: data.data.total
                    }
                },

                columnsClasses: {

                    banned_host_ip: 'banned_host_ip',

                    banned_host_comments: 'banned_host_comments',

                    banned_host_date: 'banned_host_date	',
                },

                templates: {

                    banned_host_ip(h, row) {

                        return row.banned_host_ip ? row.banned_host_ip : '---';
                    },

                    banned_host_comments(h, row) {

                        return row.banned_host_comments ? row.banned_host_comments : '---'
                    },

                    banned_host_date(h, row) {

                        return formatDateTime(row.banned_host_date, timezone, date_format, time_format)
                    },
                },

                pagination: { show : false },

                headings: {

                    banned_host_ip: this.lang('ip_address'),

                    banned_host_comments: this.lang('comments'),

                    banned_host_date: this.lang('date'),

                    actions: this.lang('actions')
                },
            }
        },

        components : {

            'data-table' : DynamicDataTable
        }
    };
</script>

<style>
    #banned_hosts .VueTables .table-responsive {
        overflow-x: auto;
        overflow-y: hidden;
    }

    #banned_hosts .VueTables .table-responsive>table {
        width: max-content;
        min-width: 100%;
        max-width: max-content;
        overflow: auto !important;
    }
</style>
