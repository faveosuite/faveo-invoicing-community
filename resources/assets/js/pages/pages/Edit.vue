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
                            <TextField name="name" :label="__('message.name')" :required="true" :value="form.name" :onChange="onChange" />
                        </div>
                        <div class="col-md-6">
                            <TextField name="slug" :label="__('message.slug')" :required="true" :value="form.slug" :onChange="onChange" />
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
                            <TextField name="url" :label="__('message.page_url')" :required="form.type !== 'contactus'" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" />
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
                        <TinyMCE name="content" :label="__('message.content')" :required="true" id="editor-content" :value="form.content" :onChange="onChange" />
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
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { useFormValidation } from '@/composables/useFormValidation'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const COMPONENT = 'pages-edit'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const route = useRoute()
const router = useRouter()

const { validate, clearFieldError, clearAllErrors } = useFormValidation()

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
    clearFieldError(name)
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
    clearAllErrors()
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
    const rules = {
        name:            [form.name,            { isRequired: __('validation.frontend_pages.name.required') }],
        slug:            [form.slug,            { isRequired: __('validation.frontend_pages.slug.required') }],
        created_at_date: [form.created_at_date, { isRequired: __('validation.publish_date_required') }],
        content:         [form.content,         { isRequired: __('validation.frontend_pages.content.required') }],
    }
    if (form.type !== 'contactus') {
        rules.url = [form.url, { isRequired: __('validation.frontend_pages.url.required') }]
    }
    const isValid = validate(rules)
    if (!isValid) return

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
