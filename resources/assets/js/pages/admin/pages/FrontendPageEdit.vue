<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_page') }}</h4>
            </div>

            <inline-loader v-if="loading" context="card-body" />

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" :error="errors.name" />
                        </div>
                        <div class="col-md-6">
                            <TextField name="slug" :label="__('message.slug')" :required="true" :value="form.slug" :onChange="onChange" :error="errors.slug" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <StaticSelect
                                name="type"
                                :label="__('message.type')"
                                :elements="[{ id: '', name: __('message.custom') }, { id: 'contactus', name: __('message.contact_us') }]"
                                :value="form.type"
                                :onChange="onChange"
                                :hideEmptySelect="true"
                            />
                        </div>
                        <div class="col-md-6">
                            <TextField name="url" :label="__('message.page_url')" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" :error="errors.url" />
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <DynamicSelect
                                name="parent_page_id"
                                :label="__('message.parent-page')"
                                :apiEndpoint="`${baseUrl}/pages`"
                                dataKey="data"
                                :value="form.parentObj"
                                :onChange="onChange"
                                :placeholder="__('message.select_parent_page')"
                            />
                        </div>
                        <div class="col-md-4">
                            <DatePicker
                                name="created_at_date"
                                :label="__('message.publish-date')"
                                :required="true"
                                :value="form.created_at_date"
                                :onChange="onChange"
                                format="YYYY-MM-DD"
                                :error="errors.created_at_date"
                            />
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">{{ __('message.publish') }}</label>
                                <Switch name="publish" :value="form.publish" :onChange="(val) => form.publish = val" />
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label fw-bold d-block">{{ __('message.set_as_default_page') }}</label>
                                <Switch name="is_default" :value="form.is_default" :onChange="(val) => form.is_default = val" />
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <TinyMCE name="content" :label="__('message.content')" :required="true" id="editor-content" :value="form.content" :onChange="onChange" :error="errors.content" />
                    </div>
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
                    <action-button action="cancel" to="/pages" class="ms-2" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildFrontendPageEditSchema } from '@/validations/admin/pageValidations'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const COMPONENT = 'pages-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)

const form = reactive({
    name:           '',
    slug:           '',
    url:            '',
    type:           '',
    publish:        false,
    content:        '',
    parent_page_id: null,
    parentObj:      null,
    created_at_date: '',
    is_default:     false,
})

function onChange(val, name) {
    setFieldError(name, undefined)
    if (name === 'parent_page_id') {
        form.parentObj = val
        form.parent_page_id = val?.id ?? null
    } else {
        form[name] = val
        if (name === 'type' && val === 'contactus') {
            form.url = `${baseUrl}/contact-us`
        }
    }
}

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/page/${route.params.id}`)
        const p = res.data?.data ?? res.data

        form.name           = p.name ?? ''
        form.slug           = p.slug ?? ''
        form.url            = p.url ?? ''
        form.type           = p.type ?? ''
        form.publish        = Boolean(p.publish)
        form.content        = p.content ?? ''
        form.parent_page_id = p.parent_page_id ?? null
        form.is_default     = Boolean(p.is_default)

        if (p.parent) {
            form.parentObj = { id: p.parent_page_id, name: p.parent.name }
        }

        if (p.created_at) {
            const d = new Date(p.created_at)
            form.created_at_date = d.toISOString().substring(0, 10)
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function submit() {
    try {
        buildFrontendPageEditSchema(form.type).validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        let created_at = ''
        if (form.created_at_date) {
            const [y, m, d] = form.created_at_date.split('-')
            created_at = `${m}/${d}/${y}`
        }

        const res = await http.put(`${baseUrl}/page/${route.params.id}`, {
            name:           form.name,
            slug:           form.slug,
            url:            form.url,
            type:           form.type,
            publish:        form.publish ? 1 : 0,
            content:        form.content,
            parent_page_id: form.parent_page_id,
            created_at,
            default_page_id: form.is_default ? route.params.id : null,
        })
        successHandler(res, COMPONENT)
        setTimeout(() => router.push('/pages'), 2000)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
