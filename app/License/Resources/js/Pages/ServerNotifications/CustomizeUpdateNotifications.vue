<template>
    <div>
        <AppAlert componentName="custom-update-note" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('customize_update_notifications') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <text-field :label="lang('notification_operation_ok')" :value="notification_operation_ok"
                            type="textarea" name="notification_operation_ok" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_operation_ok">
                        </text-field>

                        <text-field :label="lang('notification_product_not_found')" :value="notification_product_not_found"
                            type="textarea" name="notification_product_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_product_not_found">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_product_inactive')" :value="notification_product_inactive"
                            type="textarea" name="notification_product_inactive" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_product_inactive">
                        </text-field>

                        <text-field :label="lang('notification_product_no_versions')" :value="notification_product_no_versions"
                            type="textarea" name="notification_product_no_versions" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_product_no_versions">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_version_not_found')" :value="notification_version_not_found"
                            type="textarea" name="notification_version_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_version_not_found">
                        </text-field>

                        <text-field :label="lang('notification_version_inactive')" :value="notification_version_inactive"
                            type="textarea" name="notification_version_inactive" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_version_inactive">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_version_expired')" :value="notification_version_expired"
                            type="textarea" name="notification_version_expired" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_version_expired">
                        </text-field>

                        <text-field :label="lang('notification_install_limit_reached')" :value="notification_install_limit_reached"
                            type="textarea" name="notification_install_limit_reached" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_install_limit_reached">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_upgrade_limit_reached')" :value="notification_upgrade_limit_reached"
                            type="textarea" name="notification_upgrade_limit_reached" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_upgrade_limit_reached">
                        </text-field>

                        <text-field :label="lang('notification_install_archive_not_found')" :value="notification_install_archive_not_found"
                            type="textarea" name="notification_install_archive_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_install_archive_not_found">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_install_query_not_found')" :value="notification_install_query_not_found"
                            type="textarea" name="notification_install_query_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_install_query_not_found">
                        </text-field>

                        <text-field :label="lang('notification_upgrade_archive_not_found')" :value="notification_upgrade_archive_not_found"
                            type="textarea" name="notification_upgrade_archive_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_upgrade_archive_not_found">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_upgrade_query_not_found')" :value="notification_upgrade_query_not_found"
                            type="textarea" name="notification_upgrade_query_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_upgrade_query_not_found">
                        </text-field>

                        <text-field :label="lang('notification_raw_install_query_not_found')" :value="notification_raw_install_query_not_found"
                            type="textarea" name="notification_raw_install_query_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_raw_install_query_not_found">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_raw_upgrade_query_not_found')" :value="notification_raw_upgrade_query_not_found"
                            type="textarea" name="notification_raw_upgrade_query_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_raw_upgrade_query_not_found">
                        </text-field>

                        <text-field :label="lang('notification_installation_not_verified')" :value="notification_installation_not_verified"
                            type="textarea" name="notification_installation_not_verified" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_installation_not_verified">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_invalid_parameter')" :value="notification_invalid_parameter"
                            type="textarea" name="notification_invalid_parameter" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_invalid_parameter">
                        </text-field>

                        <text-field :label="lang('notification_invalid_signature')" :value="notification_invalid_signature"
                            type="textarea" name="notification_invalid_signature" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_invalid_signature">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_host_banned')" :value="notification_host_banned"
                            type="textarea" name="notification_host_banned" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_host_banned">
                        </text-field>

                        <text-field :label="lang('notification_unknown_error')" :value="notification_unknown_error"
                            type="textarea" name="notification_unknown_error" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_unknown_error">
                        </text-field>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="onSubmit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { lang } from '@/helpers/extraLogics'
import { useForm } from 'vee-validate'
import { buildNotificationsSchema } from '@/validations/admin/licenseValidations'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const { errors, setErrors, setFieldError } = useForm()
const loading = ref(true)
const saving = ref(false)
const notification_id = ref('')
const notification_operation_ok = ref('')
const notification_product_not_found = ref('')
const notification_product_inactive = ref('')
const notification_product_no_versions = ref('')
const notification_version_not_found = ref('')
const notification_version_inactive = ref('')
const notification_version_expired = ref('')
const notification_install_limit_reached = ref('')
const notification_upgrade_limit_reached = ref('')
const notification_install_archive_not_found = ref('')
const notification_install_query_not_found = ref('')
const notification_upgrade_archive_not_found = ref('')
const notification_upgrade_query_not_found = ref('')
const notification_raw_install_query_not_found = ref('')
const notification_raw_upgrade_query_not_found = ref('')
const notification_installation_not_verified = ref('')
const notification_invalid_parameter = ref('')
const notification_invalid_signature = ref('')
const notification_host_banned = ref('')
const notification_unknown_error = ref('')

const fields = [
    'notification_operation_ok', 'notification_product_not_found', 'notification_product_inactive',
    'notification_product_no_versions', 'notification_version_not_found', 'notification_version_inactive',
    'notification_version_expired', 'notification_install_limit_reached', 'notification_upgrade_limit_reached',
    'notification_install_archive_not_found', 'notification_install_query_not_found',
    'notification_upgrade_archive_not_found', 'notification_upgrade_query_not_found',
    'notification_raw_install_query_not_found', 'notification_raw_upgrade_query_not_found',
    'notification_installation_not_verified', 'notification_invalid_parameter',
    'notification_invalid_signature', 'notification_host_banned', 'notification_unknown_error'
]

const fieldRefs = {
    notification_operation_ok, notification_product_not_found, notification_product_inactive,
    notification_product_no_versions, notification_version_not_found, notification_version_inactive,
    notification_version_expired, notification_install_limit_reached, notification_upgrade_limit_reached,
    notification_install_archive_not_found, notification_install_query_not_found,
    notification_upgrade_archive_not_found, notification_upgrade_query_not_found,
    notification_raw_install_query_not_found, notification_raw_upgrade_query_not_found,
    notification_installation_not_verified, notification_invalid_parameter,
    notification_invalid_signature, notification_host_banned, notification_unknown_error,
}

function onChange(value, name) {
    setFieldError(name, undefined)
    if (fieldRefs[name] !== undefined) {
        fieldRefs[name].value = value ? value : ''
    }
}

function updateStatesWithData(data) {
    Object.keys(fieldRefs).forEach(key => {
        if (data[key] !== undefined) fieldRefs[key].value = data[key]
    })
    if (data.id) notification_id.value = data.id
}

function getInitialValues() {
    loading.value = true
    axios.get(baseUrl + '/api/admin/showUpdateNotifications').then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

function onSubmit() {
    const data = {}
    fields.forEach(field => { data[field] = fieldRefs[field].value })
    try {
        buildNotificationsSchema(fields).validateSync(data, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
    saving.value = true

    axios.post(baseUrl + '/api/admin/updateNotifications/' + notification_id.value, data).then(res => {
        successHandler(res, 'custom-update-note')
        getInitialValues()
    }).catch(err => {
        errorHandler(err, 'custom-update-note')
    }).finally(() => {
        saving.value = false
    })
}

onBeforeMount(() => {
    getInitialValues()
})
</script>
