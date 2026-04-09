<template>

	<div class="col-sm-12">

		<div class="row" v-if="!hasDataPopulated || loading">

			<custom-loader :duration="4000"></custom-loader>
		</div>

		<alert componentName="product" />

		<div class="card card-light" v-if="hasDataPopulated">

			<div class="card-header">

				<h3 class="card-title">{{lang(title)}}</h3>
			</div>

			<div class="card-body">

				<div class="row">

					<text-field :label="lang('product_name')" :value="product_title" type="text" name="product_title"
						:onChange="onChange" classname="col-sm-6" :required="true">

					</text-field>

					<radio-button :options="radioOptions" :label="lang('product_status')" name="product_status"
						:value="product_status" :onChange="onChange" classname="form-group col-sm-6">

					</radio-button>
				</div>

				<div class="row">

					<text-field :label="lang('product_sku')" :value="product_sku" type="text" name="product_sku"
						:onChange="onChange" classname="col-sm-6" :required="true">

					</text-field>

					<text-field :label="lang('product_homepage_url')" :value="product_url_homepage" type="text"
						name="product_url_homepage" :onChange="onChange" classname="col-sm-6">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="lang('product_download_url')" :value="product_url_download" type="text"
						name="product_url_download" :onChange="onChange" classname="col-sm-6">

					</text-field>

                    <text-field :label="lang('product_description')" :value="product_description" type="textarea"
                                name="product_description" :onChange="onChange" classname="col-sm-6">

                    </text-field>

                </div>
			</div>

			<div class="card-footer">

				<button class="btn btn-primary" @click="onSubmit()"><i
						:class="iconClass"></i>&nbsp;&nbsp;{{lang(btnName)}}</button>
			</div>
		</div>
	</div>
</template>

<script>

	import axios from 'axios'

	import { successHandler, errorHandler } from '../../helpers/responseHandler';

	import { getIdFromUrl } from '../../helpers/extraLogics';

	import { validateProductSettings } from "../../helpers/validator/productValidation.js";

    import { computed }  from 'vue';
    import { useStore } from 'vuex';

    import TextField from "../../components/Reusable/FormField/TextField.vue";

    import RadioButton from "../../components/Reusable/FormField/RadioButton.vue";

    import NumberField from "../../components/Reusable/FormField/NumberField.vue";

	export default {

		name: 'product-create-edit',

        setup() {

            const store = useStore();

            return {
                // getter
                getApiKey: computed(() => store.getters.getApiKey)
            };
        },

		data() {

			return {

				title: 'create_new_product',

				iconClass: 'fas fa-save',

				btnName: 'save',

				hasDataPopulated: false,

				loading: false,

				radioOptions: [{ name: 'active', value: 1 }, { name: 'inactive', value: 0 }],

				product_title: '',

				product_sku: '',

				product_status: 1,

				product_description: '',

				product_url_homepage: '',

				product_url_download: '',

				product_envato_id: '',

				apiEndpoint: '',

				product_id: ''
			}
		},

		beforeMount() {

			const path = window.location.pathname

			this.getValues(path);
		},

		methods: {

			getValues(path) {

				const productId = getIdFromUrl(path)

				if (path.indexOf('edit') >= 0) {

					this.title = 'edit_product'

					this.iconClass = 'fas fa-sync'

					this.btnName = 'update'

					this.hasDataPopulated = false

					this.getInitialValues(productId);

					this.product_id = productId;

					this.apiEndpoint = '/api/admin/updateProduct';

				} else {

					this.loading = false;

					this.hasDataPopulated = true;

					this.apiEndpoint = '/api/admin/addProduct';
				}
			},

			getInitialValues(id) {

				this.loading = true

				axios.get('/api/admin/product/' + id).then(res => {

					this.loading = false;

					this.hasDataPopulated = true

					this.updateStatesWithData(res.data.data.product);

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
			},

			isValid() {

				const { errors, isValid } = validateProductSettings(this.$data);

				return isValid;
			},

			onChange(value, name) {

                if(name === 'product_status') {

                    this[name] = value;

                } else {

                    this[name] = value ? value : '';
                }
			},

			onSubmit() {

				if (this.isValid()) {

					this.loading = true

					const data = {};

					if (this.product_id) {

						data['product_id'] = this.product_id;
					}

					data['api_key_secret'] = this.getApiKey;

					data['product_title'] = this.product_title;

					data['product_sku'] = this.product_sku;

					data['product_status'] = this.product_status ? 1 : 0;

					data['product_description'] = this.product_description;

					data['product_url_homepage'] = this.product_url_homepage;

					data['product_url_download'] = this.product_url_download;

					data['product_envato_id'] = this.product_envato_id;

					axios.post(this.apiEndpoint, data).then(res => {

						this.loading = false

						successHandler(res, 'product')

						if (!this.product_id) {

							setTimeout(() => {

								this.$router.push('/products')

							}, 2000)

						} else {

							this.getInitialValues(this.product_id)
						}

					}).catch(err => {

						this.loading = false

						errorHandler(err, 'product')
					});
				}
			}
		},

		components: {

			"text-field": TextField,

			"radio-button": RadioButton,

			"number-field": NumberField,
		}
	}
</script>
