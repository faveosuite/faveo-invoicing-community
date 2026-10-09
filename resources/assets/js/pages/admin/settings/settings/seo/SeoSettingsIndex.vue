<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.seo_general_settings') }}</h4>
            </div>

            <div v-if="loadingSettings" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <SeoMetaField
                                name="favicon_title"
                                :label="__('message.meta_title_admin')"
                                :maxLength="60"
                                :value="settingsForm.favicon_title"
                                :onChange="onSettingChange"
                                :shortcodes="SEO_ITEM_SHORTCODES"
                            />
                        </div>
                        <div class="col-md-6">
                            <SeoMetaField
                                name="favicon_title_client"
                                :label="__('message.meta_title_client')"
                                :maxLength="60"
                                :value="settingsForm.favicon_title_client"
                                :onChange="onSettingChange"
                                :shortcodes="SEO_ITEM_SHORTCODES"
                            />
                        </div>
                        <div class="col-md-12">
                            <SeoMetaField
                                name="general_description"
                                type="textarea"
                                :label="__('message.seo_general_description')"
                                :tooltip="__('message.seo_general_description_hint')"
                                :maxLength="160"
                                :value="settingsForm.general_description"
                                :onChange="onSettingChange"
                                :shortcodes="SEO_ITEM_SHORTCODES"
                            />
                        </div>
                    </div>

                    <hr>
                    <h6 class="mb-3">{{ __('message.open_graph') }}</h6>
                    <Checkbox
                        name="general_og_same_as_meta"
                        :label="__('message.seo_same_as_meta')"
                        :value="generalOgSameAsMeta"
                        :onChange="onGeneralOgSameAsMetaChange"
                        classname="mb-3"
                    />
                    <div class="row">
                        <div class="col-md-12">
                            <SeoMetaField
                                name="general_og_title"
                                :label="__('message.seo_general_og_title')"
                                :maxLength="70"
                                :value="settingsForm.general_og_title"
                                :onChange="onSettingChange"
                                :disabled="generalOgSameAsMeta"
                                :shortcodes="SEO_ITEM_SHORTCODES"
                            />
                        </div>
                        <div class="col-md-12">
                            <SeoMetaField
                                name="general_og_description"
                                type="textarea"
                                :label="__('message.seo_general_og_description')"
                                :maxLength="200"
                                :value="settingsForm.general_og_description"
                                :onChange="onSettingChange"
                                :disabled="generalOgSameAsMeta"
                                :shortcodes="SEO_ITEM_SHORTCODES"
                            />
                        </div>
                    </div>
                    <h6 class="mb-1">{{ __('message.seo_general_og_image') }}</h6>
                    <p class="text-muted small">{{ __('message.seo_general_og_image_hint') }}</p>
                    <div class="row image-upload-no-caption">
                        <ImageUpload
                            :label="__('message.seo_general_og_image')"
                            :labelStyle="{ display: 'none' }"
                            :value="generalOgImagePreview"
                            name="general_og_image"
                            :onChange="onGeneralImageChange"
                            classname="col-sm-4"
                            :componentName="COMPONENT"
                            :allowedTypes="OG_IMAGE_TYPES"
                        />
                    </div>
                </div>
                <div class="card-footer">
                    <action-button action="save" :loading="savingSettings" @click="saveSettings" />
                </div>
            </template>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, watch, onMounted } from 'vue'
import http from '@/plugins/axios'
import { successHandler, errorHandler } from '@/helpers/responseHandler.js'
import ImageUpload from '@/components/Reusable/FormField/ImageUpload.vue'
import SeoMetaField from '@/components/Reusable/FormField/SeoMetaField.vue'
import Checkbox from '@/components/Reusable/FormField/Checkbox.vue'
import { SEO_ITEM_SHORTCODES } from '@/core/utils/seoShortcodes'

const COMPONENT = 'seo-settings-index'
// Matches the mimes:jpeg,png,jpg,webp rule on the backend (SeoSettingsController).
const OG_IMAGE_TYPES = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp']

const loadingSettings = ref(true)
const savingSettings  = ref(false)

const generalOgImagePreview  = ref('')
const selectedGeneralOgImage = ref(null)
const selectedGeneralOgImageName = ref('')

const settingsForm = reactive({
    favicon_title: '',
    favicon_title_client: '',
    general_description: '',
    general_og_title: '',
    general_og_description: '',
})

const generalOgSameAsMeta = ref(false)

function onSettingChange(val, name) {
    settingsForm[name] = val
}

function onGeneralOgSameAsMetaChange(val) {
    generalOgSameAsMeta.value = val
    if (val) {
        settingsForm.general_og_title = settingsForm.favicon_title_client
        settingsForm.general_og_description = settingsForm.general_description
    }
}

// While "same as meta" is checked, keep the OG fields mirrored to the
// meta fields as the user edits them.
watch(() => settingsForm.favicon_title_client, (val) => {
    if (generalOgSameAsMeta.value) settingsForm.general_og_title = val
})
watch(() => settingsForm.general_description, (val) => {
    if (generalOgSameAsMeta.value) settingsForm.general_og_description = val
})

function onGeneralImageChange(value) {
    generalOgImagePreview.value = value.image
    selectedGeneralOgImage.value = value.file
    selectedGeneralOgImageName.value = value.name
}

onMounted(async () => {
    try {
        const res = await http.get('/seo/settings')
        const data = res.data?.data ?? {}
        settingsForm.favicon_title = data.favicon_title ?? ''
        settingsForm.favicon_title_client = data.favicon_title_client ?? ''
        settingsForm.general_description = data.general_description ?? ''
        settingsForm.general_og_title = data.general_og_title ?? ''
        settingsForm.general_og_description = data.general_og_description ?? ''
        generalOgImagePreview.value = data.general_og_image ?? ''
        generalOgSameAsMeta.value = Boolean(data.general_og_same_as_meta)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        loadingSettings.value = false
    }
})

async function saveSettings() {
    savingSettings.value = true
    try {
        const fd = new FormData()
        fd.append('favicon_title', settingsForm.favicon_title ?? '')
        fd.append('favicon_title_client', settingsForm.favicon_title_client ?? '')
        fd.append('general_description', settingsForm.general_description ?? '')
        fd.append('general_og_title', settingsForm.general_og_title ?? '')
        fd.append('general_og_description', settingsForm.general_og_description ?? '')
        fd.append('general_og_same_as_meta', generalOgSameAsMeta.value ? 1 : 0)
        if (selectedGeneralOgImage.value) {
            fd.append('general_og_image', selectedGeneralOgImage.value, selectedGeneralOgImageName.value)
        }

        const res = await http.post('/seo/settings', fd, {
            headers: { 'Content-Type': 'multipart/form-data' },
        })
        successHandler(res, COMPONENT)
    } catch (e) {
        errorHandler(e, COMPONENT)
    } finally {
        savingSettings.value = false
    }
}
</script>

<style scoped>
/* ImageUpload renders its own caption below the image (a fw-light h6);
   we show our own heading above instead, so force that one off here. */
.image-upload-no-caption :deep(h6) {
    display: none !important;
}

/* ImageUpload centers the circular preview (margin: auto); left-align it
   to match the heading/hint text above instead. */
.image-upload-no-caption :deep(.image-container) {
    margin: 0 !important;
}
</style>
