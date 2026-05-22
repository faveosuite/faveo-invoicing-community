<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.footer_widget') }}</h4>
            </div>
            <div class="card-body">
                <inline-loader v-if="loading" />
                <template v-else>
                    <ul class="nav nav-tabs mb-3">
                        <li v-for="ft in footerTypes" :key="ft.key" class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === ft.key }"
                                @click="activeTab = ft.key"
                            >
                                {{ ft.label }}
                            </button>
                        </li>
                    </ul>

                    <template v-for="ft in footerTypes" :key="ft.key">
                        <div v-if="activeTab === ft.key">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <TextField
                                        name="name"
                                        :label="__('message.name')"
                                        :required="true"
                                        :value="forms[ft.key].name"
                                        :onChange="(val) => { setFieldError('name', undefined); forms[ft.key].name = val }"
                                        :error="errors.name"
                                    />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold d-block">{{ __('message.publish') }}</label>
                                    <Switch :name="`publish-${ft.key}`" :value="forms[ft.key].publish" :onChange="(val) => forms[ft.key].publish = val" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold d-block">{{ __('message.allow_mailchimp') }}</label>
                                    <Switch :name="`allow_mailchimp-${ft.key}`" :value="forms[ft.key].allow_mailchimp" :disabled="!mailchimpStatus" :onChange="(val) => forms[ft.key].allow_mailchimp = val" />
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold d-block">{{ __('message.allow_social_media_icons') }}</label>
                                    <Switch :name="`allow_social_media-${ft.key}`" :value="forms[ft.key].allow_social_media" :onChange="(val) => forms[ft.key].allow_social_media = val" />
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-bold">{{ __('message.content') }}</label>
                                <TinyMCE
                                    name="content"
                                    :id="`editor-${ft.key}`"
                                    :value="forms[ft.key].content"
                                    :onChange="(val) => forms[ft.key].content = val"
                                />
                            </div>
                            <div class="mt-3">
                                <action-button action="save" :loading="saving[ft.key]" @click="save(ft.key)" />
                            </div>
                        </div>
                    </template>
                </template>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { footerWidgetSchema } from '@/validations/widgetValidations'
import Switch from '@/components/Reusable/FormField/Switch.vue'

const COMPONENT = 'footer-widget'
const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''

const footerTypes = [
    { key: 'footer1', label: 'Footer 1' },
    { key: 'footer2', label: 'Footer 2' },
    { key: 'footer3', label: 'Footer 3' },
]

const { errors, setErrors, setFieldError } = useForm()
const loading         = ref(true)
const activeTab       = ref('footer1')
const mailchimpStatus = ref(false)

const widgetIds = reactive({ footer1: null, footer2: null, footer3: null })

const forms = reactive({
    footer1: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
    footer2: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
    footer3: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
})

const saving = reactive({ footer1: false, footer2: false, footer3: false })

onMounted(async () => {
    try {
        const res = await http.get(`${baseUrl}/widgets/list`, { params: { limit: 200 } })
        const pages = res.data?.data?.pages?.data ?? []

        for (const ft of footerTypes) {
            const found = pages.find(w => w.type === ft.key)
            if (found) {
                widgetIds[ft.key] = found.id
                const detail = await http.get(`${baseUrl}/widgets/show/${found.id}`)
                const d = detail.data?.data?.widget ?? {}
                mailchimpStatus.value = Boolean(detail.data?.data?.mailchimpStatus)
                Object.assign(forms[ft.key], {
                    name:               d.name ?? '',
                    publish:            Boolean(d.publish),
                    allow_mailchimp:    Boolean(d.allow_mailchimp),
                    allow_social_media: Boolean(d.allow_social_media),
                    content:            d.content ?? '',
                })
            }
        }
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loading.value = false
    }
})

async function save(type) {
    try {
        footerWidgetSchema.validateSync({ name: forms[type].name }, { abortEarly: false })
    } catch (err) {
        const errMap = {}
        err.inner?.forEach(e => { if (e.path && !errMap[e.path]) errMap[e.path] = e.message })
        setErrors(errMap)
        return
    }
    saving[type] = true
    try {
        const payload = {
            name:               forms[type].name,
            type,
            publish:            forms[type].publish ? 1 : 0,
            allow_mailchimp:    forms[type].allow_mailchimp ? 1 : 0,
            allow_social_media: forms[type].allow_social_media ? 1 : 0,
            content:            forms[type].content,
        }

        let res
        if (widgetIds[type]) {
            res = await http.put(`${baseUrl}/widgets/update/${widgetIds[type]}`, payload)
        } else {
            res = await http.post(`${baseUrl}/widgets/create`, payload)
            widgetIds[type] = res.data?.data?.id ?? null
        }
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        saving[type] = false
    }
}
</script>
