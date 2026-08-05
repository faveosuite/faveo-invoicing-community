<template>
    <div>
        <AppAlert componentName="license" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang(title) }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <dynamic-select name="product" apiEndpoint="/api/admin/viewproducts" :multiple="false" :label="lang('product')" :onChange="onChange"
                                            :value="product_title" optionLabel="product_title" :required="true" :error="errors.product">
                            </dynamic-select>
                        </div>
                        <div class="col-sm-6">
                            <text-field :label="lang('license_code')" :value="license_code" type="text" name="license_code"
                                        :onChange="onChange" :required="true" :error="errors.license_code"
                                        :inputGroupBtn="{ text: 'generate', action: generateCode }">
                            </text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <dynamic-select name="client" apiEndpoint="/api/admin/viewClients/0" :multiple="false" :label="lang('client')" :onChange="onChange"
                                            :value="client_name" optionLabel="full_name" :required="true" :error="errors.client">
                            </dynamic-select>
                        </div>
                        <div class="col-sm-6">
                            <number-field :label="lang('order_number')" :value="license_order_number"
                                          name="license_order_number" :onChange="onChange">
                            </number-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <text-field :label="lang('licensed_ip')" :value="license_ip" type="text" name="license_ip"
                                        :onChange="onChange">
                            </text-field>
                        </div>
                        <div class="col-sm-6">
                            <text-field :label="lang('licensed_domain')" :multiple="true" :elements="[]"
                                        name="license_domain" :value="license_domain" :onChange="onChange"
                                        :strlength="35" :required="false" :taggable="true" :hint="lang('domain_tip')">
                            </text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <text-field :label="lang('licensed_machine_id')" :value="license_machine_id" type="text"
                                        name="license_machine_id" :onChange="onChange">
                            </text-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <date-picker :label="lang('license_expire_date')" :value="license_expire_date" type="date"
                                         name="license_expire_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                         :clearable="true" :disabled="false" :confirm="false" :error="errors.license_expire_date">
                            </date-picker>
                        </div>
                        <div class="col-sm-6">
                            <date-picker :label="lang('license_updates_date')" :value="license_updates_date" type="date"
                                         name="license_updates_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                         :clearable="true" :disabled="false" :confirm="false" :error="errors.license_updates_date">
                            </date-picker>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <date-picker :label="lang('license_support_date')" :value="license_support_date" type="date"
                                         name="license_support_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                         :clearable="true" :disabled="false" :confirm="false" :error="errors.license_support_date">
                            </date-picker>
                        </div>
                        <div class="col-sm-6">
                            <number-field :label="lang('installations_limit')" :value="license_limit" name="license_limit"
                                          :onChange="onChange">
                            </number-field>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <radio-button :options="domainOptions" :label="lang('license_require_domain')"
                                          name="license_require_domain" :value="license_require_domain ? license_require_domain : 0" :onChange="onChange">
                            </radio-button>
                        </div>
                        <div class="col-sm-6">
                            <radio-button :options="radioOptions" :label="lang('license_status')" name="license_status"
                                          :value="license_status ? license_status : 0" :onChange="onChange">
                            </radio-button>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <text-field :label="lang('comments')" :value="license_comments" type="textarea"
                                        name="license_comments" :onChange="onChange">
                            </text-field>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button :action="isEdit ? 'update' : 'save'" :loading="saving" @click="onSubmit" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { getIdFromUrl, generateRandomString, lang } from '@/helpers/extraLogics'
import { licenseSchema } from '@/validations/admin/licenseValidations'
import { validateForm } from '@/helpers/formUtils.js'
import { DateTime } from 'luxon'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import NumberField from '@/components/Reusable/FormField/NumberField.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'

const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const title = ref('create_new_license')
const isEdit = ref(false)
const loading = ref(false)
const saving = ref(false)
const license_status = ref(1)
const radioOptions = [{ name: 'active', value: 1 }, { name: 'inactive', value: 0 }]
const license_require_domain = ref(1)
const domainOptions = [{ name: 'yes', value: 1 }, { name: 'no', value: 0 }]
const apiEndpoint = ref('')
const license_id = ref('')
const product_id = ref('')
const product_title = ref('')
const client_id = ref('')
const client_name = ref('')
const license_code = ref('')
const license_order_number = ref('')
const api_key_secret = ref('')
const license_ip = ref('')
const license_domain = ref('')
const license_machine_id = ref('')
const license_limit = ref('')
const license_expire_date = ref('')
const license_updates_date = ref('')
const license_support_date = ref('')
const license_comments = ref('')

function onChange(value, name) {
    setFieldError(name, undefined)
    if (name === 'product') {
        product_id.value = value.product_id
        product_title.value = value.product_title
        setFieldError('product', undefined)
    } else if (name === 'client') {
        client_id.value = value ? value.client_id : client_id.value
        client_name.value = value ? value.full_name : ''
        setFieldError('client', undefined)
    } else {
        const map = {
            license_code, license_order_number, license_ip, license_domain, license_machine_id, license_limit,
            license_expire_date, license_updates_date, license_support_date, license_comments,
            license_status, license_require_domain
        }
        if (map[name] !== undefined) {
            map[name].value = value !== undefined && value !== null ? value : ''
        }
    }
}

function generateCode() {
    license_code.value = generateRandomString(16)
    setFieldError('license_code', undefined)
}

async function isValid() {
    return await validateForm(licenseSchema, {
        product:              product_title.value,
        client:               client_name.value,
        license_code:         license_code.value,
        license_expire_date:  license_expire_date.value,
        license_updates_date: license_updates_date.value,
        license_support_date: license_support_date.value,
    }, setErrors)
}

function getInitialValues(id) {
    loading.value = true
    axios.get('/api/admin/license/' + id).then(res => {
        let resData = res.data.data
        let licenseData = res.data.data.license
        licenseData['api_key_secret'] = licenseData.api_key_secret ? licenseData.api_key_secret.split(',') : ''
        licenseData['license_expire_date'] = licenseData.license_expire_date ? DateTime.fromSQL(licenseData.license_expire_date).toFormat('dd-MM-yyyy') : ''
        licenseData['license_updates_date'] = licenseData.license_updates_date ? DateTime.fromSQL(licenseData.license_updates_date).toFormat('dd-MM-yyyy') : ''
        licenseData['license_support_date'] = licenseData.license_support_date ? DateTime.fromSQL(licenseData.license_support_date).toFormat('dd-MM-yyyy') : ''

        product_id.value = resData.product_name[0].product_id
        product_title.value = resData.product_name[0].product_title
        client_id.value = resData.client_name[0].client_id
        client_name.value = resData.client_name[0].full_name
        license_id.value = licenseData.id
        api_key_secret.value = licenseData.api_key_secret
        license_code.value = licenseData.license_code
        license_order_number.value = licenseData.license_order_number
        license_limit.value = licenseData.license_limit
        license_ip.value = licenseData.license_ip
        license_comments.value = licenseData.license_comments
        license_require_domain.value = licenseData.license_require_domain
        license_status.value = licenseData.license_status
        license_domain.value = licenseData.license_domain
        license_machine_id.value = licenseData.license_machine_id
        license_expire_date.value = licenseData.license_expire_date
        license_updates_date.value = licenseData.license_updates_date
        license_support_date.value = licenseData.license_support_date
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

async function onSubmit() {
    if (!await isValid()) return
    saving.value = true
    const data = {}
    if (license_id.value) data['id'] = license_id.value
    data['product_id'] = product_id.value ? product_id.value : ''
    data['license_status'] = license_status.value ? 1 : 0
    data['license_require_domain'] = license_require_domain.value ? 1 : 0
    if (client_id.value) data['client_id'] = client_id.value
    if (client_name.value) data['client_name'] = client_name.value
    if (license_order_number.value) data['license_order_number'] = license_order_number.value
    data['license_ip'] = license_ip.value
    data['license_domain'] = license_domain.value
    data['license_machine_id'] = license_machine_id.value
    if (license_limit.value) data['license_limit'] = license_limit.value
    data['license_comments'] = license_comments.value
    if (license_expire_date.value) data['license_expire_date'] = DateTime.fromFormat(String(license_expire_date.value), 'dd-MM-yyyy').toFormat('yyyy-MM-dd')
    if (license_updates_date.value) data['license_updates_date'] = DateTime.fromFormat(String(license_updates_date.value), 'dd-MM-yyyy').toFormat('yyyy-MM-dd')
    if (license_support_date.value) data['license_support_date'] = DateTime.fromFormat(String(license_support_date.value), 'dd-MM-yyyy').toFormat('yyyy-MM-dd')
    data['license_code'] = license_code.value

    axios.post(apiEndpoint.value, data).then(res => {
        successHandler(res, 'license')
        if (license_id.value) {
            getInitialValues(license_id.value)
        } else {
            setTimeout(() => { router.push('/licenses/list') }, 2000)
        }
    }).catch(err => {
        errorHandler(err, 'license')
    }).finally(() => {
        saving.value = false
    })
}

onBeforeMount(() => {
    const path = globalThis.location.pathname
    const licId = getIdFromUrl(path)
    if (path.indexOf('edit') >= 0) {
        title.value = 'edit_license'
        isEdit.value = true
        getInitialValues(licId)
        license_id.value = licId
        apiEndpoint.value = '/api/admin/license/edit'
    } else {
        apiEndpoint.value = '/api/admin/license/add'
    }
})
</script>
