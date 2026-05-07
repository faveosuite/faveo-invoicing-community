<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">Edit License Type</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <TextField name="name" label="Name *" :value="form.name" :onChange="onChange" />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="submit" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                        Update
                    </button>
                    <router-link to="/settings/license-type" class="btn btn-secondary ms-2">Cancel</router-link>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'license-type-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const loading = ref(true)
const saving = ref(false)
const form = reactive({ name: '' })

function onChange(val, name) {
    form[name] = val
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/get-license-type/${route.params.id}`)
        const d = res.data?.data ?? res.data
        form.name = d.name ?? ''
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    saving.value = true
    try {
        const res = await http.put(`${baseUrl}/update-license-type/${route.params.id}`, { name: form.name })
        successHandler(res, COMPONENT)
        router.push('/settings/license-type')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
