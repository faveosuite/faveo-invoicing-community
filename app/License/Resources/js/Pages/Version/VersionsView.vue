<template>

    <div class="row">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

        <alert componentName="version-view" />

        <div class="col-md-12 ms-2">

            <div class="card card-header-tabs card-outline">

                <div class="card-header card-header-dark card-light border-bottom">

                    <h3 class="card-title">{{product_heading}}</h3>

                    <div class="card-tools">

                        <router-link :to="'/versions/'+ id +'/edit'" v-tooltip="lang('edit')" class="btn btn-tool action-btn">

                            <i class="fas fa-edit"></i>
                        </router-link>

                        <button class="btn btn-tool action-btn" v-tooltip="lang('delete_btn')" @click="showDeleteModal()">

                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="row card-body col-md-12">

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold">{{lang('product_name')}}:</label>
                        <a :href="basePath() + '/products/' + product_id + '/edit'" v-if="product_title" class="col-sm-6 fs-7">{{product_title}}</a>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold">{{lang('version_date')}}:</label>
                        <div v-if="version_date" class="col-sm-6 fs-7">{{version_date}}</div>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold">{{lang('version_upgrade_count')}}:</label>
                        <div v-if="version_upgrade_count" class="col-sm-6 fs-7">{{version_upgrade_count}}</div>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold">{{lang('version_status')}}:</label>
                        <div v-if="version_status" class="col-sm-6 text-sm text-success">{{lang('active')}}</div>
                        <div v-else class="col-sm-6 text-sm text-danger">{{lang('inactive')}}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 ms-2">

            <div class="card card-header-tabs">

                <div class="card-header data-table-header p-0 pt-1">

                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">

                        <li class="nav-item">

                            <span class="nav-link active" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" aria-controls="custom-tabs-one-home">{{lang('callbacks')}}</span>
                        </li>
                    </ul>
                </div>

                <div class="card-body">

                    <data-table v-if="!loading" :url="endPoint" ref="dataTable" :show_pagination="true" :dataColumns="columns" :option="options" scroll_to="licenses-list">

                    </data-table>
                </div>

            </div>

        </div>

        <transition name="modal">

            <delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" alertComponentName="version-view" deleteUrl="/api/admin/versions/delete" redirectUrl="/versions/list" keyVal="version_id" :idVal="id">

            </delete-modal>
        </transition>

    </div>

</template>

<script>
import {boolean, formatDateTime, getIdFromUrl, lang} from "../../helpers/extraLogics";
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import axios from "axios";
import DeleteModal from "../../components/Reusable/DeleteModal.vue";
import {h} from "vue";

export default {
    name: "version-view",

    data() {

        return {

            endPoint : '',

            showModal : false,

            hasDataPopulated: false,

            product_heading: '',

            product_title: '',

            version_date: '',

            version_upgrade_count: '',

            version_status: null,

            columns: [],

            options: {},

            loading: false,

            id: null,

            product_id: null,
        }
    },

    components: {
        'delete-modal': DeleteModal,

        'data-table': DynamicDataTable
    },

    beforeMount() {

        const path = window.location.pathname

        this.getValues(path);
    },

    props : {
    },

    methods: {
        lang,

        showDeleteModal(){

            this.showModal = !this.showModal;
        },

        onClose(){

            this.showModal = false;

            this.$store.dispatch('unsetValidationError');
        },

        getValues(path) {

            const versionId = getIdFromUrl(path)

            this.hasDataPopulated = false

            this.getInitialValues(versionId);

            this.updateData(versionId)
        },

        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/versionView/' + id).then(res => {

                this.loading = false;

                this.hasDataPopulated = true

                this.updateStatesWithData(res.data.data);

            }).catch(error => {

                this.loading = false;
            });
        },

        updateStatesWithData(data) {

            const self = this;

            const stateData = this.$data;

            Object.keys(data).map(key => {

                if (stateData.hasOwnProperty(key)) {

                    self[key] = data[key];
                }
            });

            this.product_title = data.product?.name

            this.product_id = data.product?.id

            this.product_heading = `${data.product?.name ?? ''} - ${data.version_number}`

            if(data.version_date) {

                this.version_date = data.version_date
            }
        },

        updateData(versionId) {


            this.id = versionId ? versionId : this.id

            this.loading = true

            this.endPoint = '/api/admin/versionCallbacks/' + this.id

            this.columns = ['callback_ip', 'callback_type', 'callback_date_time', 'callback_status']

            this.options = {

                    sortIcon: {

                        base: 'glyphicon',

                        up: 'glyphicon-chevron-down',

                        down: 'glyphicon-chevron-up'
                    },

                    texts: { filter: '', limit: '' },

                    sortable:  ['callback_type', 'callback_date_time', 'callback_status'],

                    filterable : [ 'callback_date_time' ],

                    requestAdapter(data) {

                        return {

                            'sort_field' : data.orderBy ? data.orderBy : 'id',

                            'sort_order' : data.ascending ? 'desc' : 'asc',

                            'search_query' : data.query.trim(),

                            perPage : data.limit,
                        }
                    },

                    responseAdapter({data}) {

                        return {

                            data: data.data.data.map(data => {

                                data.keyVal = 'version_id';

                                data.idVal = data.version_id;

                                return data;
                            }),
                            count: data.data.total
                        }
                    },

                    columnsClasses: {

                        callback_ip: 'callback_ip',

                        callback_type: 'type',

                        callback_date_time: 'callback_date_time',

                        callback_status: 'callback_status',

                    },

                    templates: {

                        callback_date_time(h, row) {

                            return row.callback_date_time
                        },

                        callback_status: (f, row) => {

                            return h('span', {
                                'class': row.callback_status ? 'text-success' : 'text-danger'
                            }, row.callback_status ? this.lang('active'): this.lang('inactive'))
                        },
                    },

                    pagination: { show : false },

                    headings: {

                        callback_ip: this.lang('ip_address'),

                        callback_type: this.lang('type'),

                        callback_date_time: this.lang('date'),

                        callback_status: this.lang('status'),

                        actions: this.lang('actions')
                    },
                }

            this.loading = false

        }

    },
}
</script>

<style scoped>

.data-table-header {
    background-color: #ebebeb;
    border-bottom: 0;
}
.card-header-dark{
    background-color: #f8f9fa;
}
.action-btn{
    color: rgba(31, 45, 61, .8);
}
.action-btn:hover{
    color: black;
}

</style>
