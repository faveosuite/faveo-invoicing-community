<template>
    <div>
        <AppAlert componentName="whitelist" />

        <div class="card card-light" v-if="hasDataPopulated">
            <div class="card-header">
                <h4 class="card-title">{{ lang(title) }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <text-field :label="lang('ip_address')" :value="whitelist_host_ip" :onChange="onChange" name="whitelist_host_ip"
                                type="text" classname="col-sm-6" :required="true">
                    </text-field>

                    <text-field :label="lang('comments')" type="text" :value="whitelist_host_comments" :onChange="onChange"
                                name="whitelist_host_comments" classname="col-sm-6">
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
import { getIdFromUrl, lang } from '@/helpers/extraLogics'
import TextField from '@/components/Reusable/FormField/TextField.vue'

const router = useRouter()
const baseUrl = document.getElementById('app-root')?.dataset?.baseUrl ?? ''

const title = ref('add_new_whitelist_ip')
const iconClass = ref('fas fa-save')
const btnName = ref('save')
const hasDataPopulated = ref(false)
const loading = ref(false)
const apiEndpoint = ref('')
const whitelist_host_comments = ref(null)
const whitelist_host_ip = ref(null)
const hostId = ref(null)

function onChange(value, name) {
    const propertyMap = {
        'whitelist_host_ip': whitelist_host_ip,
        'whitelist_host_comments': whitelist_host_comments,
    }
    if (propertyMap[name] !== undefined) {
        propertyMap[name].value = value
    }
}

function updateStatesWithData(data) {
    if (data.whitelist_host_ip) whitelist_host_ip.value = data.whitelist_host_ip
    if (data.whitelist_host_comments) whitelist_host_comments.value = data.whitelist_host_comments
}

function getInitialValues(id) {
    loading.value = true
    axios.get(baseUrl + '/api/admin/whitelist-edit/' + id).then(res => {
        loading.value = false
        hasDataPopulated.value = true
        updateStatesWithData(res.data.data.host_data)
    }).catch(() => {
        loading.value = false
    })
}

function onSubmit() {
    loading.value = true
    const formData = {
        whitelist_host_ip: whitelist_host_ip.value,
        whitelist_host_comments: whitelist_host_comments.value,
    }
    if (hostId.value) formData['id'] = hostId.value

    axios.post(apiEndpoint.value, formData).then(res => {
        loading.value = false
        successHandler(res, 'whitelist')
        if (!hostId.value) {
            setTimeout(() => { router.push('/whitelist/list') }, 2000)
        } else {
            getInitialValues(hostId.value)
        }
    }).catch(err => {
        loading.value = false
        errorHandler(err, 'whitelist')
    })
}

onBeforeMount(() => {
    const path = window.location.pathname
    const id = getIdFromUrl(path)
    if (path.includes('edit')) {
        title.value = 'edit_whitelist'
        iconClass.value = 'fas fa-sync'
        btnName.value = 'update'
        hasDataPopulated.value = false
        getInitialValues(id)
        hostId.value = id
    } else {
        loading.value = false
        hasDataPopulated.value = true
    }
    apiEndpoint.value = baseUrl + '/api/admin/whitelist/updateOrCreate'
})
</script>
