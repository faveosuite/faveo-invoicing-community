<template>
    <div :class="bare ? '' : 'card card-light mt-4'">
        <div v-if="!bare" class="card-header">
            <h5 class="card-title mb-0">{{ __('message.seo') }}</h5>
        </div>
        <div :class="bare ? '' : 'card-body'">
            <div class="row">
                <div class="col-md-12">
                    <SeoMetaField
                        name="meta_title"
                        :label="__('message.meta_title')"
                        :tooltip="__('message.meta_title_hint')"
                        :placeholder="__('message.meta_title_placeholder')"
                        :maxLength="60"
                        :value="form.meta_title"
                        :onChange="onChange"
                        :error="errors.meta_title"
                        :shortcodes="SEO_ITEM_SHORTCODES"
                    />
                </div>
                <div class="col-md-12">
                    <SeoMetaField
                        name="meta_description"
                        type="textarea"
                        :label="__('message.meta_description')"
                        :tooltip="__('message.meta_description_hint')"
                        :placeholder="__('message.meta_description_placeholder')"
                        :maxLength="160"
                        :value="form.meta_description"
                        :onChange="onChange"
                        :error="errors.meta_description"
                        :shortcodes="SEO_ITEM_SHORTCODES"
                    />
                </div>
            </div>

            <hr>
            <h6 class="mb-3">{{ __('message.open_graph') }}</h6>
            <Checkbox
                name="og_same_as_meta"
                :label="__('message.seo_same_as_meta')"
                :value="ogSameAsMeta"
                :onChange="onOgSameAsMetaChange"
                classname="mb-3"
            />
            <div class="row">
                <div class="col-md-12">
                    <SeoMetaField
                        name="og_title"
                        :label="__('message.og_title')"
                        :tooltip="__('message.og_title_hint')"
                        :placeholder="__('message.meta_title_placeholder')"
                        :maxLength="70"
                        :value="form.og_title"
                        :onChange="onChange"
                        :error="errors.og_title"
                        :disabled="ogSameAsMeta"
                        :shortcodes="SEO_ITEM_SHORTCODES"
                    />
                </div>
                <div class="col-md-12">
                    <SeoMetaField
                        name="og_description"
                        type="textarea"
                        :label="__('message.og_description')"
                        :tooltip="__('message.og_description_hint')"
                        :placeholder="__('message.meta_description_placeholder')"
                        :maxLength="200"
                        :value="form.og_description"
                        :onChange="onChange"
                        :error="errors.og_description"
                        :disabled="ogSameAsMeta"
                        :shortcodes="SEO_ITEM_SHORTCODES"
                    />
                </div>
            </div>
            <h6 class="mb-1">{{ __('message.og_image') }}</h6>
            <p class="text-muted small">{{ __('message.og_image_hint') }}</p>
            <div class="row image-upload-no-caption">
                <ImageUpload
                    :label="__('message.og_image')"
                    :labelStyle="{ display: 'none' }"
                    :value="ogImagePreview"
                    name="og_image"
                    :onChange="onImageChange"
                    classname="col-sm-4"
                    :componentName="componentName"
                    :allowedTypes="OG_IMAGE_TYPES"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch } from 'vue'
import { __ } from '@/plugins/i18n'
import { SEO_ITEM_SHORTCODES } from '@/core/utils/seoShortcodes'
import SeoMetaField from './SeoMetaField.vue'
import ImageUpload from './ImageUpload.vue'
import Checkbox from './Checkbox.vue'

// Matches the mimes:jpeg,png,jpg,webp rule on the backend (PageRequest/
// GroupRequest/SeoDefaultPageRequest).
const OG_IMAGE_TYPES = ['image/png', 'image/jpg', 'image/jpeg', 'image/webp']

const props = defineProps({
    form:           { type: Object, required: true },
    errors:         { type: Object, default: () => ({}) },
    onChange:       { type: Function, required: true },
    ogSameAsMeta:   { type: Boolean, default: false },
    ogImagePreview: { type: String, default: '' },
    componentName:  { type: String, required: true },
    // When this component is the whole page (e.g. an SEO-only edit screen
    // already wrapped in its own card), skip the card/header chrome and
    // render just the fields.
    bare:           { type: Boolean, default: false },
})

const emit = defineEmits(['update:ogSameAsMeta', 'image-change'])

function onOgSameAsMetaChange(val) {
    emit('update:ogSameAsMeta', val)
    if (val) {
        props.onChange(props.form.meta_title, 'og_title')
        props.onChange(props.form.meta_description, 'og_description')
    }
}

function onImageChange(value) {
    emit('image-change', value)
}

// While "same as meta" is checked, keep og_title/og_description mirrored
// to meta_title/meta_description as the user edits them.
watch(() => props.form.meta_title, (val) => {
    if (props.ogSameAsMeta) props.onChange(val, 'og_title')
})
watch(() => props.form.meta_description, (val) => {
    if (props.ogSameAsMeta) props.onChange(val, 'og_description')
})
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
