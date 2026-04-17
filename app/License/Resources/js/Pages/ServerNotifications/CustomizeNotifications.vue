<template>

	<div class="col-sm-12">

		<div class="row" v-if="!hasDataPopulated || loading">

			<custom-loader :duration="4000"></custom-loader>
		</div>

		<alert componentName="custom-note" />

		<div class="card card-light" v-if="hasDataPopulated">

			<div class="card-header">

				<h3 class="card-title">{{trans('customize_notifications')}}</h3>
			</div>

			<div class="card-body">

				<div class="row">

					<text-field :label="trans('notification_product_not_found')" :value="notification_product_not_found"
						type="textarea" name="notification_product_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_product_inactive')" :value="notification_product_inactive"
						type="textarea" name="notification_product_inactive" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_license_ok')" :value="notification_license_ok"
						type="textarea" name="notification_license_ok" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_license_not_found')" :value="notification_license_not_found"
						type="textarea" name="notification_license_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_invalid_ip')" :value="notification_invalid_ip"
						type="textarea" name="notification_invalid_ip" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_invalid_domain')" :value="notification_invalid_domain"
						type="textarea" name="notification_invalid_domain" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_domain_required')" :value="notification_domain_required"
						type="textarea" name="notification_domain_required" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_domain_in_use')" :value="notification_domain_in_use"
						type="textarea" name="notification_domain_in_use" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_license_suspended')" :value="notification_license_suspended"
						type="textarea" name="notification_license_suspended" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_license_expired')" :value="notification_license_expired"
						type="textarea" name="notification_license_expired" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_updates_expired')" :value="notification_updates_expired"
						type="textarea" name="notification_updates_expired" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_support_expired')" :value="notification_support_expired"
						type="textarea" name="notification_support_expired" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_license_cancelled')" :value="notification_license_cancelled"
						type="textarea" name="notification_license_cancelled" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_license_limit')" :value="notification_license_limit"
						type="textarea" name="notification_license_limit" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_installation_not_found')"
						:value="notification_installation_not_found" type="textarea"
						name="notification_installation_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_invalid_signature')" :value="notification_invalid_signature"
						type="textarea" name="notification_invalid_signature" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_host_banned')" :value="notification_host_banned"
						type="textarea" name="notification_host_banned" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>

					<text-field :label="trans('notification_unknown_error')" :value="notification_unknown_error"
						type="textarea" name="notification_unknown_error" :onChange="onChange" classname="col-sm-6"
						:required="true">

					</text-field>
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

	import { validateCustomNoteSettings } from "../../helpers/validator/customNoteValidation";

    import TextField from "../../components/Reusable/FormField/TextField.vue";

	export default {

		name: 'customize-notifications',

		data() {

			return {

				hasDataPopulated: false,

				loading: false,

				notification_id: '',

				notification_product_not_found: '',

				notification_product_inactive: '',

				notification_license_ok: '',

				notification_license_not_found: '',

				notification_invalid_ip: '',

				notification_invalid_domain: '',

				notification_domain_required: '',

				notification_domain_in_use: '',

				notification_license_suspended: '',

				notification_license_expired: '',

				notification_updates_expired: '',

				notification_support_expired: '',

				notification_license_cancelled: '',

				notification_license_limit: '',

				notification_installation_not_found: '',

				notification_invalid_signature: '',

				notification_host_banned: '',

				notification_unknown_error: '',
			}
		},

		beforeMount() {

			this.getInitialValues();
		},

		methods: {

			getInitialValues() {

				this.loading = true

				axios.get('/api/admin/viewNotifications').then(res => {

					this.loading = false;

					this.hasDataPopulated = true;

					let resData = res.data.data;

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

				if (data.id) {

					self.notification_id = data.id;
				}
			},

			isValid() {

				const { errors, isValid } = validateCustomNoteSettings(this.$data);

				return isValid;
			},

			onChange(value, name) {

				this[name] = value ? value : '';
			},

			onSubmit() {

				if (this.isValid()) {

					this.loading = true

					const data = {};

					data['notification_product_not_found'] = this.notification_product_not_found;
					data['notification_product_inactive'] = this.notification_product_inactive;
					data['notification_license_ok'] = this.notification_license_ok;
					data['notification_license_not_found'] = this.notification_license_not_found;
					data['notification_invalid_ip'] = this.notification_invalid_ip;
					data['notification_invalid_domain'] = this.notification_invalid_domain;
					data['notification_domain_required'] = this.notification_domain_required;
					data['notification_domain_in_use'] = this.notification_domain_in_use;
					data['notification_license_suspended'] = this.notification_license_suspended;
					data['notification_license_expired'] = this.notification_license_expired;
					data['notification_updates_expired'] = this.notification_updates_expired;
					data['notification_support_expired'] = this.notification_support_expired;
					data['notification_license_cancelled'] = this.notification_license_cancelled;
					data['notification_license_limit'] = this.notification_license_limit;
					data['notification_installation_not_found'] = this.notification_installation_not_found;
					data['notification_invalid_signature'] = this.notification_invalid_signature;
					data['notification_host_banned'] = this.notification_host_banned;
					data['notification_unknown_error'] = this.notification_unknown_error;

					axios.post('/api/admin/notifications/' + this.notification_id, data).then(res => {

						this.loading = false

						successHandler(res, 'custom-note');

						this.getInitialValues();

					}).catch(err => {

						this.loading = false

						errorHandler(err, 'custom-note')
					});
				}
			}
		},

		components: {

			"text-field": TextField,
		}
	}
</script>
