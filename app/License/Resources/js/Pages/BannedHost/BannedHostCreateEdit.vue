<template>
    <div>
        <div class="alert alert-info">
            <span>Add new banned host to be blocked from using Auto Faveo Licenser. Enter IP address and click the 'Submit'
                button.</span>
        </div>

        <AppAlert componentName="banned-hosts" />

        <div class="card card-light" v-if="hasDataPopulated">
            <div class="card-header">
                <h4 class="card-title">{{ lang(title) }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <text-field :label="lang('ip_address')" :value="banned_host_ip" :onChange="onChange" name="banned_host_ip"
                        type="text" classname="col-sm-6" :required="true">
                    </text-field>

                    <text-field :label="lang('comments')" type="text" :value="banned_host_comments" :onChange="onChange"
                        name="banned_host_comments" classname="col-sm-6">
                    </text-field>
                </div>
            </div>

            <div class="card-footer">
                <button class="btn btn-primary" @click="onSubmit"><i :class="iconClass"></i>&nbsp;&nbsp;{{ lang(btnName) }}</button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onBeforeMount } from 'vue'
import { useRouter } from 'vue-router'
import axios from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler'
import { bannedHostValidation } from '@/helpers/validator/bannedHostValidation.js'
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const router = useRouter()
const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const title = ref('add_new_banned_host')
const iconClass = ref('fas fa-save')
const btnName = ref('save')
const hasDataPopulated = ref(false)
const loading = ref(false)
const apiEndpoint = ref('')
const banned_host_comments = ref(null)
const banned_host_ip = ref(null)
const hostId = ref(null)

function onChange(value, name) {
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
    axios.get(baseUrl + '/api/admin/viewBannedHost/' + id).then(res => {
        loading.value = false
        hasDataPopulated.value = true
        updateStatesWithData(res.data.data.banned_host_data)
    }).catch(() => {
        loading.value = false
    })
}

function isValid() {
    const data = { banned_host_ip: banned_host_ip.value, banned_host_comments: banned_host_comments.value }
    const { isValid } = bannedHostValidation(data)
    return isValid
}

function onSubmit() {
    if (isValid()) {
        loading.value = true
        const formData = {
            banned_host_ip: banned_host_ip.value,
            comments: banned_host_comments.value,
        }
        if (hostId.value) formData['id'] = hostId.value

        axios.post(apiEndpoint.value, formData).then(res => {
            loading.value = false
            successHandler(res, 'banned-hosts')
            if (!hostId.value) {
                setTimeout(() => { router.push('/banned-hosts/list') }, 2000)
            } else {
                getInitialValues(hostId.value)
            }
        }).catch(err => {
            loading.value = false
            errorHandler(err, 'banned-hosts')
        })
    }
}

onBeforeMount(() => {
    const path = window.location.pathname
    const id = getIdFromUrl(path)
    if (path.indexOf('edit') >= 0) {
        title.value = 'edit_banned_host'
        iconClass.value = 'fas fa-sync'
        btnName.value = 'update'
        hasDataPopulated.value = false
        getInitialValues(id)
        hostId.value = id
        apiEndpoint.value = baseUrl + '/api/admin/bannedHosts/edit'
    } else {
        loading.value = false
        hasDataPopulated.value = true
        apiEndpoint.value = baseUrl + '/api/admin/bannedHosts/add'
    }
})
</script>
