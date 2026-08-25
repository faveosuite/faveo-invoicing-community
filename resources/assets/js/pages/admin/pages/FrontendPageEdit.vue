<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.edit_page') }}</h4>
            </div>

            <div v-if="loading" class="row justify-content-center py-3"><loader /></div>

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

                    <SeoFieldsCard
                        :form="form"
                        :errors="errors"
                        :onChange="onChange"
                        :ogSameAsMeta="ogSameAsMeta"
                        @update:ogSameAsMeta="ogSameAsMeta = $event"
                        :ogImagePreview="ogImagePreview"
                        :componentName="COMPONENT"
                        @image-change="onImageChange"
                    />
                </div>

                <div class="card-footer">
                    <action-button action="update" :loading="saving" @click="submit" />
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
import { validateForm } from '@/helpers/formUtils.js'
import { buildFrontendPageEditSchema } from '@/validations/admin/pageValidations'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import SeoFieldsCard from '@/components/Reusable/FormField/SeoFieldsCard.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'pages-edit'
const baseUrl = useBaseUrl()
const route = useRoute()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

const loading = ref(true)
const saving = ref(false)

const ogImagePreview = ref('')
const selectedOgImage = ref(null)
const selectedOgImageName = ref('')
const ogSameAsMeta = ref(false)

function onImageChange(value) {
    ogImagePreview.value = value.image
    selectedOgImage.value = value.file
    selectedOgImageName.value = value.name
}

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
    meta_title:       '',
    meta_description: '',
    og_title:         '',
    og_description:   '',
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
        const res = await http.get(`/page/${route.params.id}`)
        const p = res.data?.data ?? res.data

        form.name           = p.name ?? ''
        form.slug           = p.slug ?? ''
        form.url            = p.url ?? ''
        form.type           = p.type ?? ''
        form.publish        = Boolean(p.publish)
        form.content        = p.content ?? ''
        form.parent_page_id = p.parent_page_id ?? null
        form.is_default     = Boolean(p.is_default)
        form.meta_title       = p.meta_title ?? ''
        form.meta_description = p.meta_description ?? ''
        form.og_title         = p.og_title ?? ''
        form.og_description   = p.og_description ?? ''
        ogImagePreview.value  = p.og_image ?? ''
        ogSameAsMeta.value    = Boolean(p.og_same_as_meta)

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
    if (!await validateForm(buildFrontendPageEditSchema(form.type), form, setErrors)) return

    saving.value = true
    try {
        let created_at = ''
        if (form.created_at_date) {
            const [y, m, d] = form.created_at_date.split('-')
            created_at = `${m}/${d}/${y}`
        }

        const fd = new FormData()
        fd.append('name', form.name)
        fd.append('slug', form.slug)
        fd.append('url', form.url ?? '')
        fd.append('type', form.type ?? '')
        fd.append('publish', form.publish ? 1 : 0)
        fd.append('content', form.content)
        fd.append('parent_page_id', form.parent_page_id ?? '')
        fd.append('created_at', created_at)
        if (form.is_default) fd.append('default_page_id', route.params.id)
        fd.append('meta_title', form.meta_title ?? '')
        fd.append('meta_description', form.meta_description ?? '')
        fd.append('og_title', form.og_title ?? '')
        fd.append('og_description', form.og_description ?? '')
        fd.append('og_same_as_meta', ogSameAsMeta.value ? 1 : 0)
        if (selectedOgImage.value) {
            fd.append('og_image', selectedOgImage.value, selectedOgImageName.value)
        }
        fd.append('_method', 'PUT')

        const res = await http.post(`/page/${route.params.id}`, fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
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
