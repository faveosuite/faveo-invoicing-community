<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-secondary card-outline">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.create_page') }}</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <TextField name="name" :label="__('message.name') + ' *'" :value="form.name" :onChange="onChange" />
                    </div>
                    <div class="col-md-6">
                        <TextField name="slug" :label="__('message.slug') + ' *'" :value="form.slug" :onChange="onChange" />
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label fw-bold">{{ __('message.type') }}</label>
                            <select class="form-select" v-model="form.type" @change="onTypeChange">
                                <option value="">{{ __('message.custom') }}</option>
                                <option value="contactus">{{ __('message.contact_us') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <TextField name="url" :label="__('message.page_url') + ' *'" :value="form.url" :onChange="onChange" :disabled="form.type === 'contactus'" />
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
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" v-model="form.publish" id="publish" />
                                <label class="form-check-label" for="publish">{{ form.publish ? __('message.active') : __('message.inactive') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">{{ __('message.content') }} *</label>
                    <TinyMCE name="content" id="editor-content" :value="form.content" :onChange="onChange" />
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
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'

const COMPONENT = 'pages-create'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const router = useRouter()

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
    if (name === 'parent_page_id') {
        form.parentObj = val
        form.parent_page_id = val?.id ?? null
    } else {
        form[name] = val
    }
}

function onTypeChange() {
    if (form.type === 'contactus') {
        form.url = `${baseUrl}/contact-us`
    }
}

async function submit() {
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
        router.push('/pages')
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving.value = false
    }
}
</script>
