<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.cache') }}</h4>
            </div>
            <div class="card-body">
                <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
                <template v-else-if="fields.length">
                    <div class="row">
                        <div v-for="field in fields" :key="field.name" class="col-sm-6">
                            <TextField
                                :name="field.name"
                                :label="field.label"
                                :required="field.required"
                                :type="field.type ?? 'text'"
                                :value="form[field.name]"
                                :error="errors[field.name]"
                                :placehold="field.placeholder ?? ''"
                                :onChange="(val) => { setFieldError(field.name, undefined); form[field.name] = val }"
                            />
                        </div>
                    </div>
                </template>
                <div v-else class="text-muted">{{ __('message.no-record') }}</div>
            </div>
            <div v-if="!loading && fields.length" class="card-footer">
                <action-button action="save" :loading="saving" @click="save" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useForm } from 'vee-validate'
import TextField from '@/components/Reusable/FormField/TextField.vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { cacheDriverSchemas } from '@/validations/admin/cacheDriverValidations.js'
import { validateForm } from '@/helpers/formUtils.js'

const COMPONENT = 'cache-driver-settings'

const route  = useRoute()
const driver = route.params.driver

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving  = ref(false)
const fields  = ref([])
const form    = reactive({})

async function load() {
    loading.value = true
    try {
        const res = await http.get(`/cache-settings/${driver}/form`)
        fields.value = res.data?.data?.fields ?? []
        fields.value.forEach(f => { form[f.name] = f.value ?? '' })
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
}

async function save() {
    const schema = cacheDriverSchemas[driver]
    if (schema && !await validateForm(schema, form, setErrors)) return

    saving.value = true
    try {
        const res = await http.post(`/cache-settings/${driver}`, { ...form })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving.value = false
    }
}

onMounted(load)
</script>
