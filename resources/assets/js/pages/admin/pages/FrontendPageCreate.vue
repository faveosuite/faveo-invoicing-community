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
                        <TextField name="url" :label="__('message.page_url')" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" :error="errors.url" />
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
                <action-button action="save" :loading="saving" @click="submit" />
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
import { validateForm } from '@/helpers/formUtils.js'
import { buildFrontendPageCreateSchema } from '@/validations/admin/pageValidations'
import StaticSelect from '@/components/Reusable/FormField/StaticSelect.vue'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import SeoFieldsCard from '@/components/Reusable/FormField/SeoFieldsCard.vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const COMPONENT = 'pages-create'
const baseUrl = useBaseUrl()
const router = useRouter()

const { errors, setErrors, setFieldError } = useForm()

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
    name:          '',
    slug:          '',
    url:           '',
    type:          '',
    publish:       false,
    content:       '',
    parent_page_id: null,
    parentObj:     null,
    meta_title:       '',
    meta_description: '',
    og_title:         '',
    og_description:   '',
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
    if (!await validateForm(buildFrontendPageCreateSchema(form.type), form, setErrors)) return

    saving.value = true
    try {
        const fd = new FormData()
        fd.append('name', form.name)
        fd.append('slug', form.slug)
        fd.append('url', form.url ?? '')
        fd.append('type', form.type ?? '')
        fd.append('publish', form.publish ? 1 : 0)
        fd.append('content', form.content)
        fd.append('parent_page_id', form.parent_page_id ?? '')
        fd.append('meta_title', form.meta_title ?? '')
        fd.append('meta_description', form.meta_description ?? '')
        fd.append('og_title', form.og_title ?? '')
        fd.append('og_description', form.og_description ?? '')
        fd.append('og_same_as_meta', ogSameAsMeta.value ? 1 : 0)
        if (selectedOgImage.value) {
            fd.append('og_image', selectedOgImage.value, selectedOgImageName.value)
        }

        const res = await http.post(`/page`, fd, {
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
