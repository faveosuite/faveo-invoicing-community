<template>
    <div>
        <AppAlert componentName="installation" />

        <div class="card card-light" v-if="hasDataPopulated">
            <div class="card-header">
                <h4 class="card-title">{{ lang('edit_installation') }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <text-field :label="lang('domain')" required :disabled="true" :value="installation_domain" type="text" name="installation_domain"
                        :onChange="onChange" classname="col-sm-3">
                    </text-field>

                    <text-field :label="lang('ip_address')" required :value="installation_ip" type="text" name="installation_ip"
                                :onChange="onChange" classname="col-sm-3">
                    </text-field>

                    <radio-button :options="statusOptions" :label="lang('status')" name="installation_status"
                        :value="installation_status" :onChange="onChange" classname="form-group col-sm-3">
                    </radio-button>

                    <radio-button :options="radioOptions" :label="lang('disable_ip')"
                        name="installation_disable_ip_verification" :value="installation_disable_ip_verification"
                        :onChange="onChange" classname="form-group col-sm-3">
                    </radio-button>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="onSubmit()"><i class="fas fa-sync"></i>&nbsp;&nbsp;{{ lang('update') }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import { validateInstallationSettings } from '@/helpers/validator/installationValidation'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import RadioButton from '@/components/Reusable/FormField/RadioButton.vue'
import store from '@/store'

const router = useRouter()
const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const hasDataPopulated = ref(false)
const loading = ref(false)
const radioOptions = [{ name: 'yes', value: 1 }, { name: 'no', value: 0 }]
const statusOptions = [{ name: 'Active', value: 1 }, { name: 'Inactive', value: 0 }]
const installation_id = ref('')
const installation_domain = ref('')
const installation_ip = ref('')
const installation_status = ref(1)
const installation_disable_ip_verification = ref(0)

function onChange(value, name) {
    if (name === 'installation_disable_ip_verification') {
        installation_disable_ip_verification.value = value
    } else if (name === 'installation_status') {
        installation_status.value = value ? 1 : 0
    } else if (name === 'installation_ip') {
        installation_ip.value = value ? value : ''
    } else if (name === 'installation_domain') {
        installation_domain.value = value ? value : ''
    }
}

function updateStatesWithData(data) {
    if (data.installation_domain !== undefined) installation_domain.value = data.installation_domain
    if (data.installation_ip !== undefined) installation_ip.value = data.installation_ip
    if (data.installation_status !== undefined) installation_status.value = data.installation_status
    if (data.installation_disable_ip_verification !== undefined) installation_disable_ip_verification.value = data.installation_disable_ip_verification
}

function isValid() {
    const data = {
        installation_ip: installation_ip.value,
        installation_domain: installation_domain.value,
        installation_status: installation_status.value,
        installation_disable_ip_verification: installation_disable_ip_verification.value,
    }
    const { isValid } = validateInstallationSettings(data)
    return isValid
}

function onSubmit() {
    if (isValid()) {
        loading.value = true
        const data = {}
        data['id'] = installation_id.value
        data['installation_ip'] = installation_ip.value
        data['installation_status'] = installation_status.value ? 1 : 0
        data['installation_disable_ip'] = installation_disable_ip_verification.value ? 1 : 0

        axios.post(baseUrl + '/api/admin/installations/edit', data).then(res => {
            loading.value = false
            if (!res.data.api_action_success || res.data.error_detected || res.data.api_error_detected) {
                store.dispatch('setAlert', { type: 'danger', message: res.data.page_message, component_name: 'installation' })
            } else if (res.data.api_action_success && res.data.action_success) {
                successHandler({ status: 200, data: { message: res.data.page_message } }, 'installation')
                setTimeout(() => { router.push('/installations') }, 2000)
            }
        }).catch(err => {
            loading.value = false
            errorHandler(err, 'installation')
        })
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const installationId = getIdFromUrl(path)
    installation_id.value = installationId
    loading.value = true
    axios.get(baseUrl + '/api/admin/installation/' + installationId).then(res => {
        loading.value = false
        hasDataPopulated.value = true
        updateStatesWithData(res.data.data.installation)
    }).catch(() => {
        loading.value = false
    })
})
</script>
