<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_page') }}</h4>
            </div>

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
                        <TextField name="url" :label="__('message.page_url')" :required="form.type !== 'contactus'" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" :error="errors.url" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold d-block">{{ __('message.publish') }}</label>
                            <Switch name="publish" :value="form.publish" :onChange="(val) => form.publish = val" />
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <TinyMCE name="content" :label="__('message.content')" :required="true" id="editor-content" :value="form.content" :onChange="onChange" :error="errors.content" />
                </div>
            </div>

            <div class="card-footer">
                <action-button action="save" :loading="saving" @click="submit" />
                <action-button action="cancel" to="/pages" class="ms-2" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { buildFrontendPageCreateSchema } from '@/validations/admin/pageValidations'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const COMPONENT = 'pages-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const saving = ref(false)

const form = reactive({
    name:          '',
    slug:          '',
    url:           '',
    type:          '',
    publish:       false,
    content:       '',
    parent_page_id: null,
    parentObj:     null,
})

watch(() => form.name, (val) => {
    form.slug = val.replace(/\s+/g, '').toLowerCase()
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

async function submit() {
    try {
        buildFrontendPageCreateSchema(form.type).validateSync(form, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }

    saving.value = true
    try {
        const res = await http.post(`${baseUrl}/page`, {
            name:           form.name,
            slug:           form.slug,
            url:            form.url,
            type:           form.type,
            publish:        form.publish ? 1 : 0,
            content:        form.content,
            parent_page_id: form.parent_page_id,
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
