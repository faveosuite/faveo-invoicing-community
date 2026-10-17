<template>
    <div>
        <AppAlert :componentName="COMPONENT" />
        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.footer_widget') }}</h4>
            </div>
            <div class="card-body">
                <div v-if="loading" class="row justify-content-center py-3"><loader /></div>
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
                                    <label class="form-label fw-bold d-flex align-items-center gap-1">
                                        <span>{{ __('message.allow_newsletter') }}</span>
                                        <Tooltip v-if="!mailchimpStatus" :message="__('message.newsletter_not_configured')" />
                                        <Tooltip v-else-if="otherFooterHasMailchimp(ft.key)" :message="__('message.mailchimp_footer_error')" />
                                    </label>
                                    <div :title="!mailchimpStatus ? __('message.newsletter_not_configured') : (otherFooterHasMailchimp(ft.key) ? __('message.mailchimp_footer_error') : '')">
                                        <Switch
                                            :name="`allow_mailchimp-${ft.key}`"
                                            :value="forms[ft.key].allow_mailchimp"
                                            :disabled="!mailchimpStatus || otherFooterHasMailchimp(ft.key)"
                                            :onChange="(val) => onMailchimpChange(ft.key, val)"
                                        />
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold d-flex align-items-center gap-1">
                                        <span>{{ __('message.allow_social_media_icons') }}</span>
                                        <Tooltip v-if="otherFooterHasSocial(ft.key)" :message="__('message.social_icon_footer_warning')" />
                                    </label>
                                    <div :title="otherFooterHasSocial(ft.key) ? __('message.social_icon_footer_warning') : ''">
                                        <Switch
                                            :name="`allow_social_media-${ft.key}`"
                                            :value="forms[ft.key].allow_social_media"
                                            :disabled="otherFooterHasSocial(ft.key)"
                                            :onChange="(val) => onSocialMediaChange(ft.key, val)"
                                        />
                                    </div>
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
import { reactive, ref, watch, onMounted } from 'vue'
import { useForm } from 'vee-validate'
import { validateForm } from '@/helpers/formUtils.js'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import { footerWidgetSchema } from '@/validations/admin/widgetValidations'
import Switch from '@/components/Reusable/FormField/Switch.vue'
import Tooltip from '@/components/Reusable/Tooltip.vue'

const COMPONENT = 'footer-widget'

const footerTypes = [
    { key: 'footer1', label: __('message.footer_1') },
    { key: 'footer2', label: __('message.footer_2') },
    { key: 'footer3', label: __('message.footer_3') },
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

const savedForms = reactive({
    footer1: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
    footer2: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
    footer3: { name: '', publish: true, allow_mailchimp: false, allow_social_media: false, content: '' },
})

const saving = reactive({ footer1: false, footer2: false, footer3: false })

function otherFooterHasSocial(type) {
    return footerTypes.some(ft => ft.key !== type && savedForms[ft.key]?.allow_social_media)
}

function otherFooterHasMailchimp(type) {
    return footerTypes.some(ft => ft.key !== type && savedForms[ft.key]?.allow_mailchimp)
}

function onSocialMediaChange(type, val) {
    if (val && otherFooterHasSocial(type)) {
        forms[type].allow_social_media = false
        return
    }
    forms[type].allow_social_media = val
}

function onMailchimpChange(type, val) {
    if (val && otherFooterHasMailchimp(type)) {
        forms[type].allow_mailchimp = false
        return
    }
    forms[type].allow_mailchimp = val
}

watch(activeTab, (newTab, oldTab) => {
    if (oldTab && savedForms[oldTab]) {
        if (otherFooterHasSocial(oldTab) && forms[oldTab].allow_social_media) {
            forms[oldTab].allow_social_media = savedForms[oldTab].allow_social_media
        }
        if (otherFooterHasMailchimp(oldTab) && forms[oldTab].allow_mailchimp) {
            forms[oldTab].allow_mailchimp = savedForms[oldTab].allow_mailchimp
        }
    }
})

onMounted(async () => {
    try {
        const res = await http.get(`/widgets/list`, { params: { limit: 200 } })
        const pages = res.data?.data?.data ?? res.data?.data?.pages?.data ?? res.data?.pages?.data ?? []

        for (const ft of footerTypes) {
            const found = pages.find(w => w.type === ft.key)
            if (found) {
                widgetIds[ft.key] = found.id
                const detail = await http.get(`/widgets/show/${found.id}`)
                const d = detail.data?.data?.widget ?? {}
                mailchimpStatus.value = Boolean(detail.data?.data?.mailchimpStatus)
                Object.assign(forms[ft.key], {
                    name:               d.name ?? '',
                    publish:            Boolean(d.publish),
                    allow_mailchimp:    Boolean(d.allow_mailchimp),
                    allow_social_media: Boolean(d.allow_social_media),
                    content:            d.content ?? '',
                })
                Object.assign(savedForms[ft.key], { ...forms[ft.key] })
            }
        }
    } catch (e) {
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        loading.value = false
    }
})

async function save(type) {
    if (!await validateForm(footerWidgetSchema, { name: forms[type].name }, setErrors)) return
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
            res = await http.put(`/widgets/update/${widgetIds[type]}`, payload)
        } else {
            res = await http.post(`/widgets/create`, payload)
            widgetIds[type] = res.data?.data?.id ?? null
        }
        successHandler(res, COMPONENT)
        Object.assign(savedForms[type], {
            name:               forms[type].name,
            publish:            forms[type].publish,
            allow_mailchimp:    forms[type].allow_mailchimp,
            allow_social_media: forms[type].allow_social_media,
            content:            forms[type].content,
        })
    } catch (e) {
        forms[type].allow_social_media = savedForms[type].allow_social_media
        forms[type].allow_mailchimp = savedForms[type].allow_mailchimp
        errorHandler(e, COMPONENT, { setErrors })
    } finally {
        saving[type] = false
    }
}
</script>
