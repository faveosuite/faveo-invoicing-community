<template>
    <div>
        <AppAlert :componentName="COMPONENT" />

        <div class="card card-light">
            <div class="card-header">
                <h4 class="card-title">{{ __('message.settings') }}</h4>
            </div>

            <div v-if="loadingSettings" class="row justify-content-center py-3"><loader /></div>

            <template v-else>
                <div class="card-body">
                    <ul class="nav nav-tabs mb-4">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'general' }" href="#" @click.prevent="tab = 'general'">
                                {{ __('message.seo_general_settings') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'pages' }" href="#" @click.prevent="tab = 'pages'">
                                {{ __('message.pages') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" :class="{ active: tab === 'groups' }" href="#" @click.prevent="tab = 'groups'">
                                {{ __('message.product_groups') }}
                            </a>
                        </li>
                    </ul>

                    <!-- Tab: General -->
                    <div v-show="tab === 'general'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.seo_general_settings') }}</h4>
                            </div>
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
                        </div>
                    </div>

                    <!-- Tab: Pages -->
                    <div v-show="tab === 'pages'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.pages') }}</h4>
                            </div>
                            <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="pages_title_format"
                                        :label="__('message.seo_pages_title_format')"
                                        :maxLength="60"
                                        :value="settingsForm.pages_title_format"
                                        :onChange="onSettingChange"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="pages_description_format"
                                        type="textarea"
                                        :label="__('message.seo_pages_description_format')"
                                        :maxLength="160"
                                        :value="settingsForm.pages_description_format"
                                        :onChange="onSettingChange"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('message.open_graph') }}</h6>
                            <Checkbox
                                name="pages_og_same_as_meta"
                                :label="__('message.seo_same_as_meta')"
                                :value="pagesOgSameAsMeta"
                                :onChange="onPagesOgSameAsMetaChange"
                                classname="mb-3"
                            />
                            <div class="row">
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="pages_og_title_format"
                                        :label="__('message.seo_pages_og_title_format')"
                                        :maxLength="70"
                                        :value="settingsForm.pages_og_title_format"
                                        :onChange="onSettingChange"
                                        :disabled="pagesOgSameAsMeta"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="pages_og_description_format"
                                        type="textarea"
                                        :label="__('message.seo_pages_og_description_format')"
                                        :maxLength="200"
                                        :value="settingsForm.pages_og_description_format"
                                        :onChange="onSettingChange"
                                        :disabled="pagesOgSameAsMeta"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                            </div>
                            <h6 class="mb-1">{{ __('message.seo_pages_og_image') }}</h6>
                            <p class="text-muted small">{{ __('message.seo_pages_og_image_hint') }}</p>
                            <div class="row image-upload-no-caption">
                                <ImageUpload
                                    :label="__('message.seo_pages_og_image')"
                                    :labelStyle="{ display: 'none' }"
                                    :value="pagesOgImagePreview"
                                    name="pages_og_image"
                                    :onChange="onPagesImageChange"
                                    classname="col-sm-4"
                                    :componentName="COMPONENT"
                                    :allowedTypes="OG_IMAGE_TYPES"
                                />
                            </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :loading="savingSettings" @click="saveSettings" />
                            </div>
                        </div>
                    </div>

                    <!-- Tab: Product Groups -->
                    <div v-show="tab === 'groups'">
                        <div class="card card-light">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('message.product_groups') }}</h4>
                            </div>
                            <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="groups_title_format"
                                        :label="__('message.seo_groups_title_format')"
                                        :maxLength="60"
                                        :value="settingsForm.groups_title_format"
                                        :onChange="onSettingChange"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="groups_description_format"
                                        type="textarea"
                                        :label="__('message.seo_groups_description_format')"
                                        :maxLength="160"
                                        :value="settingsForm.groups_description_format"
                                        :onChange="onSettingChange"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                            </div>

                            <hr>
                            <h6 class="mb-3">{{ __('message.open_graph') }}</h6>
                            <Checkbox
                                name="groups_og_same_as_meta"
                                :label="__('message.seo_same_as_meta')"
                                :value="groupsOgSameAsMeta"
                                :onChange="onGroupsOgSameAsMetaChange"
                                classname="mb-3"
                            />
                            <div class="row">
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="groups_og_title_format"
                                        :label="__('message.seo_groups_og_title_format')"
                                        :maxLength="70"
                                        :value="settingsForm.groups_og_title_format"
                                        :onChange="onSettingChange"
                                        :disabled="groupsOgSameAsMeta"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                                <div class="col-md-12">
                                    <SeoMetaField
                                        name="groups_og_description_format"
                                        type="textarea"
                                        :label="__('message.seo_groups_og_description_format')"
                                        :maxLength="200"
                                        :value="settingsForm.groups_og_description_format"
                                        :onChange="onSettingChange"
                                        :disabled="groupsOgSameAsMeta"
                                        :shortcodes="SEO_ITEM_SHORTCODES"
                                    />
                                </div>
                            </div>
                            <h6 class="mb-1">{{ __('message.seo_groups_og_image') }}</h6>
                            <p class="text-muted small">{{ __('message.seo_groups_og_image_hint') }}</p>
                            <div class="row image-upload-no-caption">
                                <ImageUpload
                                    :label="__('message.seo_groups_og_image')"
                                    :labelStyle="{ display: 'none' }"
                                    :value="groupsOgImagePreview"
                                    name="groups_og_image"
                                    :onChange="onGroupsImageChange"
                                    classname="col-sm-4"
                                    :componentName="COMPONENT"
                                    :allowedTypes="OG_IMAGE_TYPES"
                                />
                            </div>
                            </div>
                            <div class="card-footer">
                                <action-button action="save" :loading="savingSettings" @click="saveSettings" />
                            </div>
                        </div>
                    </div>
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

const tab = ref('general')

const loadingSettings = ref(true)
const savingSettings  = ref(false)

const generalOgImagePreview  = ref('')
const selectedGeneralOgImage = ref(null)
const selectedGeneralOgImageName = ref('')

const pagesOgImagePreview  = ref('')
const selectedPagesOgImage = ref(null)
const selectedPagesOgImageName = ref('')

const groupsOgImagePreview  = ref('')
const selectedGroupsOgImage = ref(null)
const selectedGroupsOgImageName = ref('')

const settingsForm = reactive({
    favicon_title: '',
    favicon_title_client: '',
    general_description: '',
    general_og_title: '',
    general_og_description: '',
    pages_title_format: '',
    groups_title_format: '',
    pages_description_format: '',
    groups_description_format: '',
    pages_og_title_format: '',
    groups_og_title_format: '',
    pages_og_description_format: '',
    groups_og_description_format: '',
})

const generalOgSameAsMeta = ref(false)
const pagesOgSameAsMeta  = ref(false)
const groupsOgSameAsMeta = ref(false)

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

function onPagesOgSameAsMetaChange(val) {
    pagesOgSameAsMeta.value = val
    if (val) {
        settingsForm.pages_og_title_format = settingsForm.pages_title_format
        settingsForm.pages_og_description_format = settingsForm.pages_description_format
    }
}

function onGroupsOgSameAsMetaChange(val) {
    groupsOgSameAsMeta.value = val
    if (val) {
        settingsForm.groups_og_title_format = settingsForm.groups_title_format
        settingsForm.groups_og_description_format = settingsForm.groups_description_format
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
watch(() => settingsForm.pages_title_format, (val) => {
    if (pagesOgSameAsMeta.value) settingsForm.pages_og_title_format = val
})
watch(() => settingsForm.pages_description_format, (val) => {
    if (pagesOgSameAsMeta.value) settingsForm.pages_og_description_format = val
})
watch(() => settingsForm.groups_title_format, (val) => {
    if (groupsOgSameAsMeta.value) settingsForm.groups_og_title_format = val
})
watch(() => settingsForm.groups_description_format, (val) => {
    if (groupsOgSameAsMeta.value) settingsForm.groups_og_description_format = val
})

function onGeneralImageChange(value) {
    generalOgImagePreview.value = value.image
    selectedGeneralOgImage.value = value.file
    selectedGeneralOgImageName.value = value.name
}

function onPagesImageChange(value) {
    pagesOgImagePreview.value = value.image
    selectedPagesOgImage.value = value.file
    selectedPagesOgImageName.value = value.name
}

function onGroupsImageChange(value) {
    groupsOgImagePreview.value = value.image
    selectedGroupsOgImage.value = value.file
    selectedGroupsOgImageName.value = value.name
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
        settingsForm.pages_title_format = data.pages_title_format ?? ''
        settingsForm.groups_title_format = data.groups_title_format ?? ''
        settingsForm.pages_description_format = data.pages_description_format ?? ''
        settingsForm.groups_description_format = data.groups_description_format ?? ''
        settingsForm.pages_og_title_format = data.pages_og_title_format ?? ''
        settingsForm.groups_og_title_format = data.groups_og_title_format ?? ''
        settingsForm.pages_og_description_format = data.pages_og_description_format ?? ''
        settingsForm.groups_og_description_format = data.groups_og_description_format ?? ''
        generalOgImagePreview.value = data.general_og_image ?? ''
        pagesOgImagePreview.value = data.pages_og_image ?? ''
        groupsOgImagePreview.value = data.groups_og_image ?? ''
        generalOgSameAsMeta.value = Boolean(data.general_og_same_as_meta)
        pagesOgSameAsMeta.value = Boolean(data.pages_og_same_as_meta)
        groupsOgSameAsMeta.value = Boolean(data.groups_og_same_as_meta)
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
        fd.append('pages_title_format', settingsForm.pages_title_format ?? '')
        fd.append('groups_title_format', settingsForm.groups_title_format ?? '')
        fd.append('pages_description_format', settingsForm.pages_description_format ?? '')
        fd.append('groups_description_format', settingsForm.groups_description_format ?? '')
        fd.append('pages_og_title_format', settingsForm.pages_og_title_format ?? '')
        fd.append('groups_og_title_format', settingsForm.groups_og_title_format ?? '')
        fd.append('pages_og_description_format', settingsForm.pages_og_description_format ?? '')
        fd.append('groups_og_description_format', settingsForm.groups_og_description_format ?? '')
        fd.append('pages_og_same_as_meta', pagesOgSameAsMeta.value ? 1 : 0)
        fd.append('groups_og_same_as_meta', groupsOgSameAsMeta.value ? 1 : 0)
        if (selectedGeneralOgImage.value) {
            fd.append('general_og_image', selectedGeneralOgImage.value, selectedGeneralOgImageName.value)
        }
        if (selectedPagesOgImage.value) {
            fd.append('pages_og_image', selectedPagesOgImage.value, selectedPagesOgImageName.value)
        }
        if (selectedGroupsOgImage.value) {
            fd.append('groups_og_image', selectedGroupsOgImage.value, selectedGroupsOgImageName.value)
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
