<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.github_settings') }}</h4>
            </div>

            <div v-if="loading" class="card-body text-center py-5">
                <span class="spinner-border text-secondary"></span>
            </div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="git_username"
                                :label="__('message.username')"
                                :value="form.git_username"
                                placeholder="Enter GitHub username"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="git_password"
                                :label="__('message.pat')"
                                type="password"
                                :value="form.git_password"
                                placeholder="Enter Personal Access Token"
                                :onChange="(val, key) => form[key] = val"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button class="btn btn-primary" @click="save" :disabled="saving">
                        <span v-if="saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i v-else class="fas fa-save me-1"></i>
                        {{ __('message.save') }}
                    </button>
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import TextField from '@/themes/adminlte/components/forms/TextField.vue'

const COMPONENT = 'github-settings'
const el      = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const loading = ref(true)
const saving  = ref(false)

const form = reactive({
    git_username: '',
    git_password: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/settings/github`)
        const d   = res.data?.data ?? {}
        Object.assign(form, {
            git_username: d.username ?? '',
            git_password: d.password ?? '',
        })
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save() {
    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/github-setting`, {
            git_username: form.git_username,
            git_password: form.git_password,
            status:       1,
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
