<template>
    <div>
        <div class="alert alert-info">
            <p>Add new license to be used. It's possible to add licenses with and without client's profile.<br><br>With
                client's profile (when client's name and email address are known): select client and product from the
                list, and click the 'Submit'
                button. Client will need to enter his email address during script installation to verify his
                license.<br><br>Without client's profile (when anonymous license needs to be issued): select product
                from the list and enter unique license
                code (entering <b>random</b> will automatically generate a random code). Client will need to enter this
                code during script installation to verify his license.<br><br>If IP address and/or domain is set,
                product will only work on
                specified IP and/or domain. If licensed domain is entered as clientdomain.com, product will work on
                clientdomain.com and clientdomain.com/any/directory. If licensed domain is entered as
                clientdomain.com/path, product will only
                work on clientdomain.com/path. It's possible to add multiple licensed IPs and/or domains by separating
                them with comma (,) symbol. If installations limit is set, client will not be able to run more copies of
                licensed product than
                specified number.<br><br>If expiration date is set, application will stop working after this date
                (expiration date can be updated at any time).</p>
        </div>

        <AppAlert componentName="license" />

        <div class="card card-light" v-if="hasDataPopulated">
            <div class="card-header">
                <h4 class="card-title">{{ lang(title) }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <dynamic-select name="product" apiEndpoint="/api/admin/viewproducts" :multiple="false" :label="lang('product')" :onChange="onChange"
                                    classname="col-sm-6" :value="product_title" optionLabel="product_title" :required="true">
                    </dynamic-select>

                    <text-field :label="lang('license_code')" :value="license_code" type="text" name="license_code"
                                :onChange="onChange" classname="col-sm-6" :required="true" :disabled="Boolean(client_name)"
                                :showNewButton="!client_name" newBtnName="generate" :onNewButtonClick="generateCode" hint="Either Client's Profile or License Code is Required">
                    </text-field>
                </div>

                <div class="row">
                    <dynamic-select name="client" apiEndpoint="/api/admin/viewClients/0" :multiple="false" :label="lang('client')" :onChange="onChange"
                                    classname="col-sm-6" :value="client_name" optionLabel="full_name" :disabled="Boolean(license_code)" hint="Either Client's Profile or License Code is Required" :required="true">
                    </dynamic-select>

                    <number-field :label="lang('order_number')" :value="license_order_number"
                                  name="license_order_number" :onChange="onChange" classname="col-sm-6">
                    </number-field>
                </div>

                <div class="row">
                    <text-field :label="lang('licensed_ip')" :value="license_ip" type="text" name="license_ip"
                                :onChange="onChange" classname="col-sm-6">
                    </text-field>

                    <text-field :label="lang('licensed_domain')" :multiple="true" :elements="[]"
                                name="license_domain" classname="col-sm-6" :value="license_domain" :onChange="onChange"
                                :strlength="35" :required="false" :taggable="true" :hint="lang('domain_tip')">
                    </text-field>
                </div>

                <div class="row">
                    <date-picker :label="lang('license_expire_date')" :value="license_expire_date" type="date"
                                 name="license_expire_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">
                    </date-picker>

                    <date-picker :label="lang('license_updates_date')" :value="license_updates_date" type="date"
                                 name="license_updates_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">
                    </date-picker>
                </div>

                <div class="row">
                    <date-picker :label="lang('license_support_date')" :value="license_support_date" type="date"
                                 name="license_support_date" :onChange="onChange" :required="true" format="DD-MM-YYYY"
                                 classname="col-sm-6" :clearable="true" :disabled="false" :confirm="false">
                    </date-picker>

                    <radio-button :options="domainOptions" :label="lang('license_require_domain')"
                                  name="license_require_domain" :value="license_require_domain ? license_require_domain : 0" :onChange="onChange"
                                  classname="form-group col-sm-6">
                    </radio-button>
                </div>

                <div class="row">
                    <number-field :label="lang('installations_limit')" :value="license_limit" name="license_limit"
                                  :onChange="onChange" classname="col-sm-6">
                    </number-field>

                    <text-field :label="lang('comments')" :value="license_comments" type="textarea"
                                name="license_comments" :onChange="onChange" classname="col-sm-6">
                    </text-field>
                </div>

                <div class="row">
                    <radio-button :options="radioOptions" :label="lang('license_status')" name="license_status"
                                  :value="license_status ? license_status : 0" :onChange="onChange" classname="form-group col-sm-6">
                    </radio-button>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="onSubmit()"><i :class="iconClass"></i>&nbsp;&nbsp;{{ lang(btnName) }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { getIdFromUrl, generateRandomString, lang } from '@/helpers/extraLogics'
import { validateLicenseSettings } from '@/helpers/validator/validateLicenseSettings.js'
import moment from 'moment'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import NumberField from '@/components/Reusable/FormField/NumberField.vue'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import DatatableDynamicSelect from '@/components/Reusable/FormField/DatatableDynamicSelect.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import DateTimePicker from '@/components/Reusable/FormField/DateTimePicker.vue'

const router = useRouter()
const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const title = ref('create_new_license')
const iconClass = ref('fas fa-save')
const btnName = ref('save')
const hasDataPopulated = ref(false)
const loading = ref(false)
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
const license_limit = ref('')
const license_expire_date = ref('')
const license_updates_date = ref('')
const license_support_date = ref('')
const license_comments = ref('')

function onChange(value, name) {
    if (name === 'product') {
        product_id.value = value.product_id
        product_title.value = value.product_title
    } else if (name === 'client') {
        client_id.value = value ? value.client_id : client_id.value
        client_name.value = value ? value.full_name : ''
    } else {
        const map = {
            license_code, license_order_number, license_ip, license_domain, license_limit,
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
}

function isValid() {
    const data = {
        product_id: product_id.value, license_code: license_code.value, client_id: client_id.value,
        client_name: client_name.value, license_status: license_status.value
    }
    const { isValid } = validateLicenseSettings(data)
    return isValid
}

function getInitialValues(id) {
    loading.value = true
    axios.get(baseUrl + '/api/admin/license/' + id).then(res => {
        let resData = res.data.data
        let licenseData = res.data.data.license
        licenseData['api_key_secret'] = licenseData.api_key_secret ? licenseData.api_key_secret.split(',') : ''
        licenseData['license_expire_date'] = licenseData.license_expire_date ? new Date(moment(licenseData.license_expire_date).format("MM-DD-YYYY")) : ''
        licenseData['license_updates_date'] = licenseData.license_updates_date ? new Date(moment(licenseData.license_updates_date).format("MM-DD-YYYY")) : ''
        licenseData['license_support_date'] = licenseData.license_support_date ? new Date(moment(licenseData.license_support_date).format("MM-DD-YYYY")) : ''

        product_id.value = resData.product_name[0].product_id
        product_title.value = resData.product_name[0].product_title
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
        license_expire_date.value = licenseData.license_expire_date
        license_updates_date.value = licenseData.license_updates_date
        license_support_date.value = licenseData.license_support_date
        loading.value = false
    }).catch(() => {
        loading.value = false
    })
}

function onSubmit() {
    if (isValid()) {
        loading.value = true
        const data = {}
        if (license_id.value) data['id'] = license_id.value
        data['product_id'] = product_id.value ? product_id.value : ''
        data['license_status'] = license_status.value ? 1 : 0
        data['license_require_domain'] = license_require_domain.value ? 1 : 0
        if (client_id.value && !license_code.value) data['client_id'] = client_id.value
        if (client_name.value) data['client_name'] = client_name.value
        if (license_order_number.value) data['license_order_number'] = license_order_number.value
        data['license_ip'] = license_ip.value
        data['license_domain'] = license_domain.value
        if (license_limit.value) data['license_limit'] = license_limit.value
        data['license_comments'] = license_comments.value
        if (license_expire_date.value) data['license_expire_date'] = moment(license_expire_date.value).format("YYYY-MM-DD")
        if (license_updates_date.value) data['license_updates_date'] = moment(license_updates_date.value).format("YYYY-MM-DD")
        if (license_support_date.value) data['license_support_date'] = moment(license_support_date.value).format("YYYY-MM-DD")
        data['license_code'] = license_code.value

        axios.post(apiEndpoint.value, data).then(res => {
            loading.value = false
            successHandler(res, 'license')
            if (!license_id.value) {
                setTimeout(() => { router.push('/licenses') }, 2000)
            } else {
                getInitialValues(license_id.value)
            }
        }).catch(err => {
            loading.value = false
            errorHandler(err, 'license')
        })
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const licId = getIdFromUrl(path)
    if (path.indexOf('edit') >= 0) {
        title.value = 'edit_license'
        iconClass.value = 'fas fa-sync'
        btnName.value = 'update'
        hasDataPopulated.value = false
        getInitialValues(licId)
        license_id.value = licId
        apiEndpoint.value = baseUrl + '/api/admin/license/edit'
    } else {
        loading.value = false
        hasDataPopulated.value = true
        apiEndpoint.value = baseUrl + '/api/admin/license/add'
    }
})
</script>
