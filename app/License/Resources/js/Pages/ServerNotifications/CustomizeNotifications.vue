<template>
    <div>
        <AppAlert componentName="custom-note" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang('customize_notifications') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <text-field :label="lang('notification_product_not_found')" :value="notification_product_not_found"
                            type="textarea" name="notification_product_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_product_not_found">
                        </text-field>

                        <text-field :label="lang('notification_product_inactive')" :value="notification_product_inactive"
                            type="textarea" name="notification_product_inactive" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_product_inactive">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_license_ok')" :value="notification_license_ok"
                            type="textarea" name="notification_license_ok" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_ok">
                        </text-field>

                        <text-field :label="lang('notification_license_not_found')" :value="notification_license_not_found"
                            type="textarea" name="notification_license_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_not_found">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_invalid_ip')" :value="notification_invalid_ip"
                            type="textarea" name="notification_invalid_ip" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_invalid_ip">
                        </text-field>

                        <text-field :label="lang('notification_invalid_domain')" :value="notification_invalid_domain"
                            type="textarea" name="notification_invalid_domain" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_invalid_domain">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_domain_required')" :value="notification_domain_required"
                            type="textarea" name="notification_domain_required" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_domain_required">
                        </text-field>

                        <text-field :label="lang('notification_domain_in_use')" :value="notification_domain_in_use"
                            type="textarea" name="notification_domain_in_use" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_domain_in_use">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_license_suspended')" :value="notification_license_suspended"
                            type="textarea" name="notification_license_suspended" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_suspended">
                        </text-field>

                        <text-field :label="lang('notification_license_expired')" :value="notification_license_expired"
                            type="textarea" name="notification_license_expired" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_expired">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_updates_expired')" :value="notification_updates_expired"
                            type="textarea" name="notification_updates_expired" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_updates_expired">
                        </text-field>

                        <text-field :label="lang('notification_support_expired')" :value="notification_support_expired"
                            type="textarea" name="notification_support_expired" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_support_expired">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_license_cancelled')" :value="notification_license_cancelled"
                            type="textarea" name="notification_license_cancelled" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_cancelled">
                        </text-field>

                        <text-field :label="lang('notification_license_limit')" :value="notification_license_limit"
                            type="textarea" name="notification_license_limit" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_license_limit">
                        </text-field>
                    </div>

                    <div class="row">
                        <text-field :label="lang('notification_installation_not_found')"
                            :value="notification_installation_not_found" type="textarea"
                            name="notification_installation_not_found" :onChange="onChange" classname="col-sm-6"
                            :required="true" :error="errors.notification_installation_not_found">
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
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const { errors, setErrors, setFieldError } = useForm()
const loading = ref(true)
const saving = ref(false)
const notification_id = ref('')
const notification_product_not_found = ref('')
const notification_product_inactive = ref('')
const notification_license_ok = ref('')
const notification_license_not_found = ref('')
const notification_invalid_ip = ref('')
const notification_invalid_domain = ref('')
const notification_domain_required = ref('')
const notification_domain_in_use = ref('')
const notification_license_suspended = ref('')
const notification_license_expired = ref('')
const notification_updates_expired = ref('')
const notification_support_expired = ref('')
const notification_license_cancelled = ref('')
const notification_license_limit = ref('')
const notification_installation_not_found = ref('')
const notification_invalid_signature = ref('')
const notification_host_banned = ref('')
const notification_unknown_error = ref('')

const fieldRefs = {
    notification_product_not_found, notification_product_inactive, notification_license_ok,
    notification_license_not_found, notification_invalid_ip, notification_invalid_domain,
    notification_domain_required, notification_domain_in_use, notification_license_suspended,
    notification_license_expired, notification_updates_expired, notification_support_expired,
    notification_license_cancelled, notification_license_limit, notification_installation_not_found,
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
    axios.get('/api/admin/viewNotifications').then(res => {
        updateStatesWithData(res.data.data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

async function onSubmit() {
    const data = {}
    Object.keys(fieldRefs).forEach(key => { data[key] = fieldRefs[key].value })
    if (!await validateForm(buildNotificationsSchema(Object.keys(fieldRefs)), data, setErrors)) return
    saving.value = true

    axios.post('/api/admin/notifications/' + notification_id.value, data).then(res => {
        successHandler(res, 'custom-note')
        getInitialValues()
    }).catch(err => {
        errorHandler(err, 'custom-note', { setErrors })
    }).finally(() => {
        saving.value = false
    })
}

onBeforeMount(() => {
    getInitialValues()
})
</script>
