<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.github_settings') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="git_username"
                                :label="__('message.username')"
                                :value="form.git_username"
                                placeholder="Enter GitHub username"
                                :required="true"
                                :error="errors.git_username"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>
                        <div class="col-md-6 mb-3">
                            <TextField
                                name="git_password"
                                :label="__('message.pat')"
                                type="password"
                                :value="form.git_password"
                                placeholder="Enter Personal Access Token"
                                :required="true"
                                :error="errors.git_password"
                                :onChange="(val, key) => { setFieldError(key, undefined); form[key] = val }"
                            />
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="save" :loading="saving" @click="save" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { validateForm } from '@/helpers/formUtils.js'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import { githubSchema } from '@/validations/admin/githubValidations'

const COMPONENT = 'github-settings'

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving  = ref(false)

const form = reactive({
    git_username: '',
    git_password: '',
})

onMounted(async () => {
    try {
        const res = await http.get(`/settings/github`)
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
    if (!await validateForm(githubSchema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/github-setting`, {
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
