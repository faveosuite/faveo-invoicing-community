<template>

	<div class="col-sm-12">

		<div class="row" v-if="!hasDataPopulated || loading">

			<custom-loader :duration="4000"></custom-loader>
		</div>

		<alert componentName="custom-update-note" />

		<div class="card card-light" v-if="hasDataPopulated">

			<div class="card-header">

				<h3 class="card-title">{{trans('customize_update_notifications')}}</h3>
			</div>

			<div class="card-body">

				<div class="row">

					<text-field :label="trans('notification_operation_ok')" :value="notification_operation_ok"
						type="textarea" name="notification_operation_ok" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_product_not_found')" :value="notification_product_not_found"
						type="textarea" name="notification_product_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_product_inactive')" :value="notification_product_inactive"
						type="textarea" name="notification_product_inactive" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_product_no_versions')" :value="notification_product_no_versions"
						type="textarea" name="notification_product_no_versions" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_version_not_found')" :value="notification_version_not_found"
						type="textarea" name="notification_version_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_version_inactive')" :value="notification_version_inactive"
						type="textarea" name="notification_version_inactive" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_version_expired')" :value="notification_version_expired"
						type="textarea" name="notification_version_expired" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_install_limit_reached')" :value="notification_install_limit_reached"
						type="textarea" name="notification_install_limit_reached" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_upgrade_limit_reached')" :value="notification_upgrade_limit_reached"
						type="textarea" name="notification_upgrade_limit_reached" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_install_archive_not_found')" :value="notification_install_archive_not_found"
						type="textarea" name="notification_install_archive_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_install_query_not_found')" :value="notification_install_query_not_found"
						type="textarea" name="notification_install_query_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_upgrade_archive_not_found')" :value="notification_upgrade_archive_not_found"
						type="textarea" name="notification_upgrade_archive_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_upgrade_query_not_found')" :value="notification_upgrade_query_not_found"
						type="textarea" name="notification_upgrade_query_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_raw_install_query_not_found')" :value="notification_raw_install_query_not_found"
						type="textarea" name="notification_raw_install_query_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_raw_upgrade_query_not_found')" :value="notification_raw_upgrade_query_not_found"
						type="textarea" name="notification_raw_upgrade_query_not_found" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>

					<text-field :label="trans('notification_installation_not_verified')" :value="notification_installation_not_verified"
						type="textarea" name="notification_installation_not_verified" :onChange="onChange" classname="col-sm-6"
						:required="true">
					</text-field>
				</div>

				<div class="row">

					<text-field :label="trans('notification_invalid_parameter')" :value="notification_invalid_parameter"
						type="textarea" name="notification_invalid_parameter" :onChange="onChange" classname="col-sm-6"
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

    import TextField from "../../components/Reusable/FormField/TextField.vue";

	export default {

		name: 'customize-update-notifications',

		data() {

			return {

				hasDataPopulated: false,

				loading: false,

				notification_id: '',

				notification_operation_ok: '',

				notification_product_not_found: '',

				notification_product_inactive: '',

				notification_product_no_versions: '',

				notification_version_not_found: '',

				notification_version_inactive: '',

				notification_version_expired: '',

				notification_install_limit_reached: '',

				notification_upgrade_limit_reached: '',

				notification_install_archive_not_found: '',

				notification_install_query_not_found: '',

				notification_upgrade_archive_not_found: '',

				notification_upgrade_query_not_found: '',

				notification_raw_install_query_not_found: '',

				notification_raw_upgrade_query_not_found: '',

				notification_installation_not_verified: '',

				notification_invalid_parameter: '',

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

				axios.get('/api/admin/showUpdateNotifications').then(res => {

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

			onChange(value, name) {

				this[name] = value ? value : '';
			},

			onSubmit() {

				this.loading = true

				const data = {};

				const fields = [
					'notification_operation_ok', 'notification_product_not_found', 'notification_product_inactive',
					'notification_product_no_versions', 'notification_version_not_found', 'notification_version_inactive',
					'notification_version_expired', 'notification_install_limit_reached', 'notification_upgrade_limit_reached',
					'notification_install_archive_not_found', 'notification_install_query_not_found',
					'notification_upgrade_archive_not_found', 'notification_upgrade_query_not_found',
					'notification_raw_install_query_not_found', 'notification_raw_upgrade_query_not_found',
					'notification_installation_not_verified', 'notification_invalid_parameter',
					'notification_invalid_signature', 'notification_host_banned', 'notification_unknown_error'
				];

				fields.forEach(field => { data[field] = this[field]; });

				axios.post('/api/admin/updateNotifications/' + this.notification_id, data).then(res => {

					this.loading = false

					successHandler(res, 'custom-update-note');

					this.getInitialValues();

				}).catch(err => {

					this.loading = false

					errorHandler(err, 'custom-update-note')
				});
			}
		},

		components: {

			"text-field": TextField,
		}
	}
</script>
