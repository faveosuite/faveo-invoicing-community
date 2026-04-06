<template>

	<div class="col-sm-12">

		<div class="row" v-if="loading">

			<custom-loader :duration="4000"></custom-loader>
		</div>

		<alert componentName="dataTableModal" />

		<div class="card card-light ">

			<div class="card-header">

				<h3 class="card-title">{{lang('all_installations')}}</h3>
			</div>

			<div class="card-body" id="my_installations">

                <data-table :url="endPoint" :show_pagination="true" alertComponentName="dataTableModal" :dataColumns="columns" :option="options" scroll_to="licenses-list">

                </data-table>

            </div>
		</div>
	</div>
</template>

<script>

import {formatDateTime, lang} from '../../helpers/extraLogics'
import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
import {useStore} from "vuex";
import {computed, h} from "vue";
import {RouterLink} from "vue-router";

	export default {

		name: 'installations-list',

        methods : {
            lang
        },

        setup() {

            const store = useStore();

            return {

                formattedTime : computed(()=>store.getters.formattedTime)
            }
        },

        props : {
        },

		data() {

			return {

				data: '',

				columns: ['product_title', 'license', 'client_email', 'installation_domain', 'installation_ip', 'installation_date', 'installation_status', 'actions'],

				options: {},

				counter: 0,

				loading: false,

                endPoint : '/api/admin/viewInstallations?page=1',
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

                sortable:  ['product_title', 'installation_status'],

                filterable : [ 'product_title' ],

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

                            data.edit_url = '/installations/' + data.id + '/edit';

                            data.delete_url = '/api/admin/installations/delete';

                            data.view_url = '/installations/' + data.id + '/view';

                            data.keyVal = 'id';

                            data.idVal = data.id;

                            return data;
                        }),
                        count: data.data.total
                    }
                },

				columnsClasses: {

					product_title: 'product_title',

					license: 'i_license_code',

                    client_email: 'client_email',

                    installation_domain: 'installation_domain',

                    installation_ip: 'installation_ip',

					installation_date: 'i_latest_installation',

					installation_status: 'i_installation_status',
				},

				templates: {

                    installation_date(h, row){

                        return row.installation_date
                    },

                    product_title: (f, row) => {

                        if(row.product_title && row.product_id) {

                            return h('a', {

                                href: self.basePath() + '/products/' + row.product_id + '/edit'

                            },[row.product_title])

                        } else {
                            return '----'
                        }
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

                    client_email: (f, row) => {

                        if(row.client_email) {

                            return h('a', {

                                href: self.basePath() + '/clients/' + row.client_id

                            },[row.client_email])

                        } else {
                            return '----'
                        }
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

					product_title: this.lang('product'),

					license: this.lang('license_code'),

                    client_email: this.lang('email'),

                    installation_domain: this.lang('domain'),

                    installation_ip: this.lang('ip'),

					installation_date: this.lang('installation_date'),

					installation_status: this.lang('status'),

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
	.i_product_title,
	.i_license_code,
	.i_total_installations,
	.i_latest_installation,
	.i_installation_status {
		max-width: 200px;
		word-break: break-all;
	}

	#my_installations .VueTables .table-responsive {
		overflow-x: auto;
		overflow-y: hidden;
	}

	#my_installations .VueTables .table-responsive>table {
		width: max-content;
		min-width: 100%;
		max-width: max-content;
		overflow: auto !important;
	}
</style>
