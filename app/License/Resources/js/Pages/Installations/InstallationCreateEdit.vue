<template>

	<div class="col-sm-12">

		<div class="row" v-if="!hasDataPopulated || loading">

			<custom-loader :duration="4000"></custom-loader>
		</div>

		<alert componentName="installation" />

		<div class="card card-light" v-if="hasDataPopulated">

			<div class="card-header">

				<h3 class="card-title">{{trans('edit_installation')}}</h3>
			</div>

			<div class="card-body">

				<div class="row">

					<text-field :label="trans('domain')" required :disabled="true" :value="installation_domain" type="text" name="installation_domain"
						:onChange="onChange" classname="col-sm-3">

					</text-field>

                    <text-field :label="trans('ip_address')" required :value="installation_ip" type="text" name="installation_ip"
                                :onChange="onChange" classname="col-sm-3">

                    </text-field>

					<radio-button :options="statusOptions" :label="trans('status')" name="installation_status"
						:value="installation_status" :onChange="onChange" classname="form-group col-sm-3">

					</radio-button>

					<radio-button :options="radioOptions" :label="trans('disable_ip')"
						name="installation_disable_ip_verification" :value="installation_disable_ip_verification"
						:onChange="onChange" classname="form-group col-sm-3">

					</radio-button>
				</div>
			</div>

			<div class="card-footer">

				<button class="btn btn-primary" @click="onSubmit()"><i
						class="fas fa-sync"></i>&nbsp;&nbsp;{{trans('update')}}</button>
			</div>
		</div>
	</div>
</template>

<script>

	import axios from 'axios'

	import { successHandler, errorHandler } from '../../helpers/responseHandler';

	import { getIdFromUrl } from '../../helpers/extraLogics';

	import { validateInstallationSettings } from "../../helpers/validator/installationValidation";

    import TextField from "../../components/Reusable/FormField/TextField.vue";

    import RadioButton from "../../components/Reusable/FormField/RadioButton.vue";
    import store from "../../store";
    import {computed} from "vue";
    import {useStore} from "vuex";

	export default {

		name: 'installation-create-edit',

        setup() {

            const store = useStore();

            return {
                // getter
                getApiKey: computed(() => store.getters.getApiKey)
            };
        },

		data() {

			return {

				hasDataPopulated: false,

				loading: false,

				radioOptions: [{ name: 'yes', value: 1 }, { name: 'no', value: 0 }],

				statusOptions: [{ name: 'Active', value: 1 }, { name: 'Inactive', value: 0 }],

				installation_id: '',

                installation_domain: '',

				installation_ip: '',

				installation_status: 1,

				installation_disable_ip_verification: 0,
			}
		},

		beforeMount() {

			const path = window.location.pathname

			this.getInitialValues(path);
		},

		methods: {

			getInitialValues(path) {

				const installationId = getIdFromUrl(path)

				this.installation_id = installationId;

				this.loading = true

				axios.get('/api/admin/installation/' + installationId).then(res => {

					this.loading = false;

					this.hasDataPopulated = true;

					let resData = res.data.data.installation;

					this.updateStatesWithData(resData);

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

				const { errors, isValid } = validateInstallationSettings(this.$data);

				return isValid;
			},

            onChange(value, name) {
                if (name == 'installation_disable_ip_verification') {
                    this[name] = value;
                }else if (name == 'installation_status') {
                    this[name] = value ? 1 : 0;
                }else {
                    this[name] = value ? value : '';
                }
            },

			onSubmit() {

				if (this.isValid()) {

					this.loading = true

					const data = {};

					data['id'] = this.installation_id;

					data['installation_ip'] = this.installation_ip;

					data['installation_status'] = this.installation_status ? 1 : 0;

					data['installation_disable_ip'] = this.installation_disable_ip_verification ? 1 : 0;

					axios.post('/api/admin/installations/edit', data).then(res => {

						this.loading = false

						if (!res.data.api_action_success || res.data.error_detected || res.data.api_error_detected) {

                            store.dispatch('setAlert', { type: 'danger', message: res.data.page_message, component_name: 'installation' });

						} else if(res.data.api_action_success && res.data.action_success) {

							successHandler({ status: 200, data: { message: res.data.page_message } }, 'installation');

							setTimeout(() => {

								this.$router.push('/installations')

							}, 2000)
						}

					}).catch(err => {

						this.loading = false

						errorHandler(err, 'installation')
					});
				}
			}
		},

		components: {

			"text-field": TextField,

			"radio-button": RadioButton
		}
	}
</script>
