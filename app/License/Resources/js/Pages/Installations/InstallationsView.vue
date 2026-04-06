<template>

    <div class="container-fluid">

        <div class="col-md-12">

            <div class="row" v-if="loading">

                <custom-loader :duration="4000"></custom-loader>
            </div>

            <alert componentName="installations-view" />

            <div class="card card-header-tabs card-outline">

                <div class="card-header card-header-dark card-light">

                    <h3 class="card-title">{{product_title}}</h3>

                    <div class="card-tools">

                        <router-link :to="'/installations/'+ id +'/edit'" v-tooltip="lang('edit')" class="btn btn-tool action-btn">

                            <i class="fas fa-edit"></i>
                        </router-link>

                        <button class="btn btn-tool action-btn" v-tooltip="lang('delete_btn')" @click="showDeleteModal()">

                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>

                <div class="row card-body col-md-12 ms-2 ps-0">

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('license_code')}}:</label>
                        <router-link v-if="license_code" :to="'/licenses/' + license_id + '/view'" class="col-sm-6 fs-7">{{ license_code.match(/.{1,4}/g).join('-') }}</router-link>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('installation_date')}}:</label>
                        <div v-if="installation_date" class="col-sm-6 fs-7">{{installation_date}}</div>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('installation_domain')}}:</label>
                        <a :href="'https://'+installation_domain" target="_blank" v-if="installation_domain" class="col-sm-6 fs-7">{{installation_domain}}</a>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 border-bottom col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('installation_ip')}}:</label>
                        <div v-if="installation_ip" class="col-sm-6 fs-7">{{installation_ip}}</div>
                        <span class="col-sm-6" v-else >----</span>
                    </div>

                    <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('ip_address_verification')}}</label>
                        <div v-if="installation_disable_ip_verification" class="col-sm-6 text-sm text-success">{{lang('enabled')}}</div>
                        <div v-else class="col-sm-6 text-sm text-danger">{{lang('disabled')}}</div>
                    </div>

                    <div class="row pt-2 pb-2 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-1">{{lang('status')}}:</label>
                        <div v-if="installation_status" class="col-sm-6 text-sm text-success">{{lang('active')}}</div>
                        <div v-else class="col-sm-6 text-sm text-danger">{{lang('inactive')}}</div>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-sm-12 ms-2">

            <div class="card card-header-tabs">

                <div class="card-header data-table-header border-0 p-0 pt-1">
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

            <delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" alertComponentName="installations-view" deleteUrl="/api/admin/installations/delete" redirectUrl="/installations/list" keyVal="id" :idVal="id">

            </delete-modal>
        </transition>

    </div>

</template>

<script>
import {formatDateTime, getIdFromUrl, lang} from "../../helpers/extraLogics";
import axios from "axios";
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import DeleteModal from "../../components/Reusable/DeleteModal.vue";
import {h} from "vue";

export default {
    name: "InstallationsView.vue",
    components: {

        'delete-modal': DeleteModal,

        'data-table': DynamicDataTable
    },

    data() {

        return {

            loading: false,

            showModal: false,

            endPoint: '',

            product_title: '',

            license_code: '',

            license_id: '',

            installation_date: '',

            installation_domain: '',

            installation_ip: '',

            installation_disable_ip_verification: null,

            installation_status: null,

            columns: [],

            options: {},

            id: '',
        }
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

            const installationId = getIdFromUrl(path)

            this.hasDataPopulated = false

            this.getInitialValues(installationId);

            this.updateData('callbacks', installationId)
        },

        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/installationView/' + id).then(res => {

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

            this.product_title = data.product_title || ''
            this.installation_date = data.installation_date || ''
            this.license_code = data.license_code || ''
            this.license_id = data.license_id || ''

        },

        updateData(value, productId) {


            this.id = productId ? productId : this.id

            this.loading = true

            this.endPoint = '/api/admin/installationCallbacks/' + this.id

            this.columns = ['callback_domain','callback_ip', 'callback_date_time', 'callback_status']

            this.options = {

                    sortIcon: {

                        base: 'glyphicon',

                        up: 'glyphicon-chevron-down',

                        down: 'glyphicon-chevron-up'
                    },

                    texts: { filter: '', limit: '' },

                    sortable:  ['callback_date_time', 'callback_status'],

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

                                data.keyVal = 'id';

                                data.idVal = data.id;

                                return data;
                            }),
                            count: data.data.total
                        }
                    },

                    columnsClasses: {

                        callback_ip: 'ip_address',

                        callback_domain: 'callback_domain',

                        order_number: 'order_number',

                        callback_date_time: 'callback_date_time',

                        callback_status: 'status',
                    },

                    templates: {

                        callback_ip(h, row) {

                            return row.callback_ip ? row.callback_ip : '----'
                        },

                        callback_date_time(h, row) {

                            return row.callback_date_time
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
                    },

                    pagination: { show : false },

                    headings: {

                        callback_ip: this.lang('ip'),

                        callback_domain: this.lang('domain'),

                        order_number: this.lang('order_number'),

                        callback_date_time: this.lang('date'),

                        callback_status: this.lang('status')
                    },
                }

            this.loading = false
        }

    },
}
</script>
<style scoped>

.data-table-header {
    background-color: #ebebeb !important;
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
