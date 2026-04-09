<template>

    <div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="dataTableModal" />

        <div class="card card-light ">

            <div class="card-header">

                <h3 class="card-title">{{lang('all_versions')}}</h3>

                <div class="card-tools">

                    <router-link to="/versions/create" class="btn btn-tool" v-tooltip="lang('create_version')">

                        <i class="fas fas fa-plus"></i>
                    </router-link>
                </div>
            </div>

            <div class="card-body" id="my_products">

                <data-table :url="endPoint" :show_pagination="true" :dataColumns="columns" :option="options" scroll_to="products-list">

                </data-table>

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

    name: 'VersionsIndex',

    methods : {
        lang
    },

    props : {
        generalSetting : {type : Object, default : () => {}},
    },

    data() {

        return {

            loading: false,

            data: '',

            columns: ['version_number', 'product_title', 'version_date', 'version_upgrade_count', 'callback_count', 'version_status', 'actions'],

            options: {},

            counter: 0,

            endPoint : '/api/admin/viewVersions?page=1'
        }
    },

    beforeMount() {

        const self = this;

        const date_format = this.generalSetting.date_format.js_format;
        const time_format = this.generalSetting.time_format.js_format;
        const timezone = this.generalSetting.timezone.name;

        this.options = {

            sortIcon: {

                base: 'glyphicon',

                up: 'glyphicon-chevron-down',

                down: 'glyphicon-chevron-up'
            },

            texts: { filter: '', limit: '' },

            sortable:  ['product_title', 'version_date', 'version_upgrade_count', 'callback_count', 'version_status'],

            filterable : [ 'product_title' ],

            requestAdapter(data) {

                return {

                    'sort_field' : data.orderBy ? data.orderBy : 'version_id',

                    'sort_order' : data.ascending ? 'desc' : 'asc',

                    'search_query' : data.query.trim(),

                    perPage : data.limit,
                }
            },

            responseAdapter({data}) {

                return {

                    data: data.data.data.map(data => {

                        data.edit_url = '/versions/' + data.version_id + '/edit';

                        data.delete_url = '/api/admin/versions/delete';

                        data.view_url = '/versions/' + data.version_id + '/view';

                        data.keyVal = 'version_id';

                        data.idVal = data.version_id;

                        return data;
                    }),

                    count: data.data.total
                }
            },

            columnsClasses: {

                product_title: 'product_title',

                version_number: 'version_number',

                version_date: 'version_date',

                version_upgrade_count: 'version_upgrade_count',

                callback_count: 'callback_count',

                version_status: 'version_status',

            },

            templates: {

                version_date(h, row) {

                    return formatDateTime(row.version_date, timezone, date_format, time_format)
                },

                product_title: (f, row) => {

                    if(row.product_title && row.product_id) {

                        return h(RouterLink, {

                            to: '/products/' + row.product_id + '/view'

                        },[row.product_title])

                    } else {
                        return '----'
                    }
                },

                version_number: (f, row) => {

                    if(row.version_number && row.version_id) {

                        return h(RouterLink, {

                            to: '/versions/' + row.version_id + '/view'

                        },[row.version_number])

                    } else {
                        return '----'
                    }
                },

                version_status: (f, row) => {

                    return h('span', {
                        'class': row.version_status ? 'text-success' : 'text-danger'
                    }, row.version_status ? this.lang('active'): this.lang('inactive'))
                },

            },

            pagination: { show : false },

            headings: {

                product_title: this.lang('product'),

                version_number: this.lang('version'),

                version_date: this.lang('release_date'),

                version_upgrade_count: this.lang('upgrades_count'),

                callback_count: this.lang('callbacks_count'),

                version_status: this.lang('status'),

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
.product_title,
.product_sku,
.product_url,
.product_status,
.product_version,
.product_licenses .product_installations {
    max-width: 200px;
    word-break: break-all;
}

#my_products .VueTables .table-responsive {
    overflow-x: auto;
    overflow-y: hidden;
}

#my_products .VueTables .table-responsive>table {
    width: max-content;
    min-width: 100%;
    max-width: max-content;
    overflow: auto !important;
}
</style>
