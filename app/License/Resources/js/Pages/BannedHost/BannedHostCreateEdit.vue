<template>
    <div>
        <AppAlert componentName="banned-hosts" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ lang(title) }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <text-field :label="lang('ip_address')" :value="banned_host_ip" :onChange="onChange" name="banned_host_ip"
                            type="text" classname="col-sm-6" :required="true" :error="errors.banned_host_ip"
                            :placeholder="lang('enter_banned_host_ip')">
                        </text-field>

                        <text-field :label="lang('comments')" type="text" :value="banned_host_comments" :onChange="onChange"
                            name="banned_host_comments" classname="col-sm-6" :placeholder="lang('enter_banned_host_comments')">
                        </text-field>
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
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import { useForm } from 'vee-validate'
import { bannedHostSchema } from '@/validations/admin/licenseValidations'
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const title = ref('create_banned_host')
const isEdit = ref(false)
const loading = ref(false)
const saving = ref(false)
const apiEndpoint = ref('')
const banned_host_comments = ref(null)
const banned_host_ip = ref(null)
const hostId = ref(null)

function onChange(value, name) {
    setFieldError(name, undefined)
    if (name === 'banned_host_ip') {
        banned_host_ip.value = value
    } else if (name === 'banned_host_comments') {
        banned_host_comments.value = value
    }
}

function updateStatesWithData(data) {
    if (data.banned_host_ip) banned_host_ip.value = data.banned_host_ip
    if (data.comments) banned_host_comments.value = data.comments
}

function getInitialValues(id) {
    loading.value = true
    axios.get('/api/admin/viewBannedHost/' + id).then(res => {
        updateStatesWithData(res.data.data.banned_host_data)
    }).catch(() => {}).finally(() => {
        loading.value = false
    })
}

async function onSubmit() {
    if (!await validateForm(bannedHostSchema, { banned_host_ip: banned_host_ip.value }, setErrors)) return
    saving.value = true
    const formData = {
        banned_host_ip: banned_host_ip.value,
        comments: banned_host_comments.value,
    }
    if (hostId.value) formData['id'] = hostId.value

    axios.post(apiEndpoint.value, formData).then(res => {
        successHandler(res, 'banned-hosts')
        if (hostId.value) {
            getInitialValues(hostId.value)
        } else {
            setTimeout(() => { router.push('/banned-hosts/list') }, 2000)
        }
    }).catch(err => {
        errorHandler(err, 'banned-hosts', { setErrors })
    }).finally(() => {
        saving.value = false
    })
}

onBeforeMount(() => {
    const path = globalThis.location.pathname
    const id = getIdFromUrl(path)
    if (path.indexOf('edit') >= 0) {
        title.value = 'edit_banned_host'
        isEdit.value = true
        getInitialValues(id)
        hostId.value = id
        apiEndpoint.value = '/api/admin/bannedHosts/edit'
    } else {
        apiEndpoint.value = '/api/admin/bannedHosts/add'
    }
})
</script>
