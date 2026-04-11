<template>

    <div class="row">

        <div class="col-md-12 ms-2">

            <div class="row" v-if="loading">

                <custom-loader :duration="4000"></custom-loader>
            </div>

            <alert componentName="license-view" />

            <div class="card card-header-tabs">

                <div class="card-header card-header-dark card-light">

                    <h3 class="card-title">{{product_title}}</h3>

                    <div class="card-tools">

                        <router-link :to="'/licenses/'+ license_id +'/edit'" v-tooltip="lang('edit')" class="btn action-btn btn-tool">

                            <i class="fas text-md fa-edit"></i>
                        </router-link>

                        <button class="btn btn-tool action-btn" v-tooltip="lang('delete_btn')" @click="showDeleteModal()">

                            <i class="fas fa-trash"></i>
                        </button>

                    </div>
                </div>

                <div class="row card-body">

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('client_email')}}:</label>
                        <router-link :to="'/clients/'+client_id+'/view'" v-if="client_email" class="col-sm-6 fs-7">{{client_email}}</router-link>
                        <span class="col-sm-6 fs-7" v-else >----</span>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('product_title')}}:</label>
                        <router-link v-if="product_title" :to="'/products/'+product_id+'/view'" class="col-sm-6 fs-7">{{product_title}}</router-link>
                        <span class="col-sm-6 fs-7" v-else >----</span>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('installations')}}:</label>
                        <div v-if="installation_counts" class="col-sm-6 fs-7">{{installation_counts}}</div>
                        <span class="col-sm-6 fs-7" v-else >----</span>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('callbacks')}}:</label>
                        <div v-if="call_backs_count" class="col-sm-6 fs-7">{{call_backs_count}}</div>
                        <span class="col-sm-6 fs-7" v-else >----</span>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('latest_callback')}}:</label>
                        <div v-if="latest_call_backs" class="col-sm-6 fs-7">{{latest_call_backs}}</div>
                        <span class="col-sm-6 fs-7" v-else >----</span>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 border-bottom ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('order_number')}}:</label>
                        <a :href="extractHref(license_order_url)" target="_blank" v-if="license_order_number" class="col-sm-6 fs-7">{{license_order_number}}</a>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_ip')}}:</label>
                        <div v-if="license_ip" class="col-sm-6 fs-7">{{license_ip}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_domain')}}:</label>
                        <a v-if="license_domain" :href="'https://'+license_domain" target="_blank" class="col-sm-6 fs-7">{{license_domain}}</a>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('installation_limit')}}:</label>
                        <div v-if="installation_limit >= 0" class="col-sm-6 fs-7">{{installation_limit}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_date')}}:</label>
                        <div v-if="license_date" class="col-sm-6 fs-7">{{license_date}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_expiry')}}:</label>
                        <div v-if="license_expire_date" class="col-sm-6 fs-7">{{license_expire_date}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('updates_expiry')}}:</label>
                        <div v-if="license_updates_date" class="col-sm-6 fs-7">{{license_updates_date}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('support_expiry')}}:</label>
                        <div v-if="license_support_date" class="col-sm-6 fs-7">{{license_support_date}}</div>
                        <div v-else class="col-sm-6 fs-7">----</div>
                    </div>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">
                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_status')}}:</label>
                        <div v-if="license_status" class="col-sm-6 text-sm text-success">Active</div>
                        <div v-else class="col-sm-6 text-sm text-danger">Inactive</div>
                    </div>

                    <hr>

                    <div class="row p-1 pb-3 pt-3 col-sm-6 ms-2 ps-0">

                        <label class="col-sm-6 fs-7 fw-bold ps-0">{{lang('license_code')}}:</label>
                        <div v-if="license_code && license_code !== '----'" class="col-sm-6 fs-7">
                            {{license_code.match(/.{1,4}/g).join('-')}}
                            <span class="btn ml-2 btn-light" v-tooltip="lang('copy')" style="cursor: pointer" @click="copyCommand()">
                                    <i :class="iconClass"></i>
                            </span>
                        </div>
                        <div v-else class="col-sm-6 fs-7">----</div>

                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 ms-2">

            <div class="card card-header-tabs">

                <div class="card-header data-table-header border-0 p-0 pt-1">

                    <ul class="nav nav-tabs" id="custom-tabs-one-tab" role="tablist">

                        <li class="nav-item">
                            <span class="nav-link card-header-link cursor-pointer active" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" @click="updateData('installations')" aria-controls="custom-tabs-one-home">{{lang('installations')}}</span>
                        </li>

                        <li class="nav-item">
                            <span class="nav-link card-header-link cursor-pointer" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" @click="updateData('callbacks')" aria-controls="custom-tabs-one-home">{{lang('callbacks')}}</span>
                        </li>

                        <li class="nav-item">
                            <span class="nav-link card-header-link cursor-pointer" id="custom-tabs-one-home-tab" data-bs-toggle="pill" role="tab" @click="updateData('logs')" aria-controls="custom-tabs-one-home">{{lang('installation_logs')}}</span>
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

            <delete-modal v-if="showModal" :onClose="onClose" :showModal="showModal" alertComponentName="license-view" deleteUrl="/api/admin/license/delete" redirectUrl="/licenses/list" keyVal="license_id" :idVal="license_id">

            </delete-modal>
        </transition>
    </div>

</template>


<script>

import {formatDateTime, getIdFromUrl, lang} from "../../helpers/extraLogics";
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import axios from "axios";
import copy from "clipboard-copy";
import DeleteModal from "../../components/Reusable/DeleteModal.vue";
import {h} from "vue";

export default {
    name: "LicensesView",

    data() {

        return {

            hasDataPopulated: false,

            iconClass: 'fas fa-copy',

            showModal: false,

            license_id: null,

            loading: false,

            client_email: '',

            client_id: '',

            installation_counts: null,

            latest_call_backs: '',

            license_ip: '',

            installation_limit: null,

            license_expire_date: '',

            license_support_date: '',

            license_code: '',

            product_title: '',

            call_backs_count: null,

            license_order_number: '',

            license_order_url: '',

            license_domain: '',

            license_date: '',

            license_updates_date: '',

            license_status: null,

            product_id: '',

            endPoint: '',

            columns: [],

            options: {},
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

        copyCommand() {

            copy(this.license_code)

            this.iconClass = 'fas fa-check'

            setTimeout(()=>{
                this.iconClass = 'fas fa-copy'
            },2000)
        },

        onClose(){

            this.showModal = false;

            this.$store.dispatch('unsetValidationError');
        },

        showDeleteModal(){

            this.showModal = !this.showModal;
        },

        extractHref(orderUrl) {

            const parser = new DOMParser();

            // Parse the HTML string

            const parsedHtml = parser.parseFromString(orderUrl, 'text/html');

            // Get the root element of the parsed HTML

            const htmlElement = parsedHtml.documentElement;

            return htmlElement.querySelector('#href_link') ?? ''
        },
        getValues(path) {

            const licenseId = getIdFromUrl(path)

            this.hasDataPopulated = false

            this.getInitialValues(licenseId);

            this.updateData('installations',licenseId)
        },

        getInitialValues(id) {

            this.loading = true

            axios.get('/api/admin/licenseView/' + id).then(res => {

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

            this.product_title = data.product?.product_title

            if(data.clients) {

                this.client_email = data.clients.client_email
                this.client_id = data.clients.client_id
            }
            if(data.latest_call_backs) {

                this.latest_call_backs = data.latest_call_backs
            }
            if(data.license_date) {

                this.license_date = data.license_date
            }
            if(data.license_limit >= 0) {

                this.installation_limit = data.license_limit
            }
            if(data.license_expire_date) {

                this.license_expire_date = data.license_expire_date
            }
            if(data.license_updates_date) {

                this.license_updates_date = data.license_updates_date
            }
            if(data.license_support_date) {

                this.license_support_date = data.license_support_date
            }
        },
        updateData(value, licenseId) {




            this.id = licenseId ? licenseId : this.id

            if(value === 'installations') {

                this.loading = true

                this.endPoint = '/api/admin/licenseInstallation/' + this.id

                this.columns = ['installation_domain', 'installation_ip', 'installation_date', 'installation_status', 'actions']

                this.options = {

                    sortIcon: {

                        base: 'glyphicon',

                        up: 'glyphicon-chevron-down',

                        down: 'glyphicon-chevron-up'
                    },

                    texts: { filter: '', limit: '' },

                    sortable:  ['installation_domain', 'installation_date', 'installation_status'],

                    filterable : [ 'installation_domain'],

                    requestAdapter(data) {

                        return {

                            'sort_field' : data.orderBy ? data.orderBy : 'installation_id',

                            'sort_order' : data.ascending ? 'desc' : 'asc',

                            'search_query' : data.query.trim(),

                            perPage : data.limit,
                        }
                    },
                    responseAdapter({data}) {

                        return {

                            data: data.data.data.map(data => {

                                data.edit_url = '/installations/' + data.installation_id + '/edit';

                                data.delete_url = '/api/admin/installations/delete';

                                data.view_url = '/installations/' + data.installation_id + '/view';

                                data.keyVal = 'installation_id';

                                data.idVal = data.installation_id;

                                return data;
                            }),

                            count: data.data.total
                        }
                    },
                    columnsClasses: {

                        installation_domain: 'installation_domain',

                        installation_ip: 'installation_ip',

                        installation_date: 'installation_date',

                        installation_status: 'installation_status',
                    },

                    templates: {

                        installation_ip(h, row) {

                            return row.installation_ip ? row.installation_ip : '----'
                        },

                        installation_date(h, row) {

                            return row.installation_date
                        },

                        license_date(h, row) {

                            return row.license_date
                        },

                        installation_domain: (f, row) => {

                            if(row.installation_domain) {

                                return h('a', {

                                    href: 'https://'+row.installation_domain,
                                    target: '_blank'
                                },[row.installation_domain])

                            } else {
                                return '----'
                            }
                        },

                        installation_status: (f, row) => {

                            return h('span', {
                                'class': row.installation_status ? 'text-success' : 'text-danger'
                            }, row.installation_status ? this.lang('active'): this.lang('inactive'))
                        },

                    },

                    pagination: { show : false },

                    headings: {

                        installation_domain: this.lang('domain'),

                        installation_ip: this.lang('ip'),

                        installation_date: this.lang('installation_date'),

                        installation_status: this.lang('status'),

                        actions: this.lang('actions')
                    },
                }

                this.loading = false

            } else if(value === 'callbacks') {

                this.loading = true

                this.endPoint = '/api/admin/licenseCallbacks/' + this.id

                this.columns = ['callback_domain', 'callback_ip', 'callback_date_time', 'callback_status']

                this.options = {

                    sortIcon: {

                        base: 'glyphicon',

                        up: 'glyphicon-chevron-down',

                        down: 'glyphicon-chevron-up'
                    },

                    texts: { filter: '', limit: '' },

                    sortable:  ['callback_domain', 'callback_date', 'callback_status'],

                    filterable : [ 'callback_domain' ],

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

                        callback_domain: 'callback_domain',

                        callback_ip: 'callback_ip',

                        callback_date_time: 'callback_date_time',

                        callback_status: 'callback_status',
                    },

                    templates: {

                        callback_ip(h, row) {

                            return row.callback_ip ? row.callback_ip : '----'
                        },

                        callback_date_time(h, row) {

                            return row.callback_date_time
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

                        callback_status: (f, row) => {

                            return h('span', {
                                'class': row.callback_status ? 'text-success' : 'text-danger'
                            }, row.callback_status ? this.lang('success'): this.lang('error'))
                        },
                    },

                    pagination: { show : false },

                    headings: {

                        callback_domain: this.lang('domain'),

                        callback_ip: this.lang('ip'),

                        callback_date_time: this.lang('callback_date_time'),

                        callback_status: this.lang('status'),
                    },
                }

                this.loading = false

            } else {

                this.loading = true

                this.endPoint = '/api/admin/installationLogs/' + this.id

                this.columns = ['installation_domain', 'installation_ip', 'version_number', 'installation_last_active_date', 'installation_status']

                this.options = {

                    sortIcon: {

                        base: 'glyphicon',

                        up: 'glyphicon-chevron-down',

                        down: 'glyphicon-chevron-up'
                    },

                    texts: { filter: '', limit: '' },

                    sortable:  ['installation_domain', 'installation_last_active_date', 'installation_status'],

                    filterable : [ 'installation_domain' ],

                    requestAdapter(data) {

                        return {

                            'sort_field' : data.orderBy ? data.orderBy : 'installation_last_active_date',

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

                        installation_domain: 'installation_domain',

                        installation_ip: 'installation_ip',

                        version: 'version',

                        installation_last_active_date: 'installation_last_active_date',

                        installation_status: 'installation_status',
                    },

                    templates: {

                        installation_ip(h, row) {

                            return row.installation_ip ? row.installation_ip : '----'
                        },

                        version_number(h, row) {

                            return row.version_number ? row.version_number : '----'
                        },

                        installation_last_active_date(h, row) {

                            return row.installation_last_active_date
                        },
                        installation_domain: (f, row) => {

                            if(row.installation_domain) {

                                return h('a', {
                                    href: 'https://'+row.installation_domain,
                                    target: '_blank'
                                },[row.installation_domain])

                            } else {
                                return '----'
                            }
                        },
                        installation_status: (f, row) => {

                            return h('span', {
                                'class': row.installation_status ? 'text-success' : 'text-danger'
                            }, row.installation_status ? this.lang('active'): this.lang('inactive'))
                        },
                    },

                    pagination: { show : false },

                    headings: {

                        installation_domain: this.lang('domain'),

                        installation_ip: this.lang('ip'),

                        version_number: this.lang('version'),

                        installation_last_active_date: this.lang('last_active_date'),

                        installation_status: this.lang('status'),
                    },
                }

                this.loading = false
            }
        },
    },
    components: {

        'delete-modal': DeleteModal,

        'data-table': DynamicDataTable,
    }
}
</script>

<style scoped>

.card-header-link{
    color: black;
}
.card-header-link:hover:not(.active){
    color: #007bff;
    cursor: pointer;
}
.card-header-dark {
    background-color: #f8f9fa;
}
.data-table-header {
    background-color: #ebebeb !important;
}
.action-btn{
    color: rgba(31, 45, 61, .8);
}
.action-btn:hover{
    color: black;
}
</style>
