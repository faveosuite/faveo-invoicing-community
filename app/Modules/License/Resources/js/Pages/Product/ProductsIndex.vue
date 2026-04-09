<template>

	<div class="col-sm-12">

        <div class="row" v-if="loading">

            <custom-loader :duration="4000"></custom-loader>
        </div>

		<alert componentName="dataTableModal" />

		<div class="card card-light ">

			<div class="card-header">

				<h3 class="card-title">{{lang('all_products')}}</h3>

				<div class="card-tools">

					<router-link to="/products/create" class="btn btn-tool" v-tooltip="lang('create_product')">

						<i class="fas fa-plus"></i>
					</router-link>
				</div>
			</div>

			<div class="card-body" id="my_products">

                <data-table :url="endPoint" :show_pagination="true" alertComponentName="dataTableModal" :dataColumns="columns" :option="options" scroll_to="products-list">

                </data-table>

            </div>
		</div>
	</div>
</template>

<script>

    import {lang} from "../../helpers/extraLogics";
    import DynamicDataTable from "../../components/Reusable/DynamicDataTable.vue";
    import {h} from "vue";
    import {RouterLink} from "vue-router";

	export default {

		name: 'products-list',

        methods : {
            lang
        },

		data() {

			return {

                loading: false,

                data: '',

				columns: ['product_title', 'product_sku', 'versions', 'versions_available', 'licenses_count', 'installations_count', 'product_status', 'actions'],

				options: {},

				counter: 0,

                endPoint : '/api/admin/viewproducts?page=1'
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

                sortable:  ['product_title', 'product_sku', 'product_status'],

                filterable : [ 'product_title' ],

                requestAdapter(data) {

                    return {

                        'sort_field' : data.orderBy ? data.orderBy : 'product_id',

                        'sort_order' : data.ascending ? 'desc' : 'asc',

                        'search_query' : data.query.trim(),

                         perPage : data.limit,
                    }
                },

                responseAdapter({data}) {

                    return {

                        data: data.data.data.map(data => {

                            data.edit_url = '/products/' + data.product_id + '/edit';

                            data.delete_url = '/api/admin/allProductDelete';

                            data.tooltip = 'suspend';

                            data.modalTitle = 'suspend';

                            data.modalMessage = 'are_you_sure_to_suspend';

                            data.btnTitle = 'suspend';

                            data.view_url = '/products/' + data.product_id + '/view';

                            data.keyVal = 'product_id';

                            data.idVal = data.product_id;

                            return data;
                        }),

                        count: data.data.total
                    }
                },

				columnsClasses: {

					product_title: 'product_title',

					product_sku: 'product_sku',

					product_status: 'product_status',

                    versions: 'versions',

                    versions_available: 'versions_available',

                    licenses_count: 'product_licenses',

                    installations_count: 'product_installations'
				},

				templates: {

                    versions_available(h, row) {

                        return row.versions_count >= 0 ? row.versions_count : '----'
                    },

                    product_sku(h, row) {

                        return row.product_sku ? row.product_sku : '---'
                    },

                    total_licenses(h, row) {

                        return row.total_licenses ? row.total_licenses : '---'
                    },

                    installations_count(h, row) {

                        return row.installations_count >= 0 ? row.installations_count : '---'
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

                    versions: (f, row) => {

                        if(row.versions && row.versions.version_number) {

                            return h(RouterLink, {

                                to: '/versions/' + row.versions.version_id + '/view'

                            },[row.versions.version_number])

                        } else {
                            return '----'
                        }
                    },

                    product_status: (f, row) => {

                        return h('span', {
                                'class': row.product_status ? 'text-success' : 'text-danger'
                            }, row.product_status ? this.lang('active'): this.lang('inactive'))
                    },
				},

				pagination: { show : false },

				headings: {

					product_title: this.lang('product'),

					product_sku: this.lang('sku'),

                    versions: this.lang('latest_versions'),

					licenses_count: this.lang('licenses'),

                    installations_count: this.lang('no_of_installations'),

					product_status: this.lang('status'),

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
