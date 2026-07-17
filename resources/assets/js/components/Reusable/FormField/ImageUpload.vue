<template>
    <FormFieldTemplate :label="label" :labelStyle="labelStyle" :name="name"
                       :classname="classname" :hint="hint" :required="required">

        <input ref="fileInput" type="file" @change="onFileSelected" class="hidden-input"
               multiple :disabled="is_default" />

        <div class="image-container" @click="fileInput.click()">
            <ImageElement id="profile-pic"
                          :classes="['profile-user-img', 'img-responsive', 'img-rounder', 'img-click']"
                          :sourceUrl="previewUrl" :title="lang(tooltip)" :style-object="styleObj"
                          :placeholder-type="placeholderType" />
            <div class="camera_logo translateCameraIcon">
                <i class="fas fa-camera text-lg text-white"></i>
            </div>
        </div>

        <h6 class="text-center fs-7 fw-light mt-2 d-inline-block" :style="labelCss">{{ label }}</h6>
        <span class="d-inline-block">
            <label class="is-danger" v-if="required">*</label>
        </span>

        <AppModal v-if="showModal" :showModal="showModal" :onClose="onClose">
            <template #title>
                <div><h4>Crop Profile</h4></div>
            </template>
            <template #fields>
                <div>
                    <vue-cropper ref="cropper" :guides="true" :view-mode="2"
                                 drag-mode="crop" :auto-crop="true" :auto-crop-area="100"
                                 :min-container-width="570" :min-container-height="300"
                                 :background="true" :rotatable="true" :src="imageSrc"
                                 :img-style="crop" @crop="cropImage" :aspect-ratio="aspectRatio" />
                </div>
                <div class="rotate-button">
                    <button v-if="imageSrc" @click="rotateImage" id="rotate">
                        <i class="fas fa-sync-alt"></i>&nbsp;{{ lang('rotate') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(0)" id="ratio"
                            :class="{ 'active': aspectRatio === 0 }" class="m-1">
                        &nbsp;{{ lang('no_ratio') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(1)" id="ratio"
                            :class="{ 'active': aspectRatio === 1 }" class="m-1">
                        &nbsp;{{ '1:1 ' + lang('ratio') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(2)" id="ratio"
                            :class="{ 'active': aspectRatio === 2 }" class="m-1">
                        &nbsp;{{ '16:9 ' + lang('ratio') }}
                    </button>
                </div>
            </template>
            <template #controls>
                <button type="button" @click="onSubmit" class="btn btn-primary mt-2 float-start" id="crop_action">
                    <i class="fa fa-check"></i> {{ lang('proceed') }}
                </button>
            </template>
        </AppModal>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch, onBeforeMount, nextTick } from 'vue'
import { lang } from '@/helpers/extraLogics'
import { useAlertStore } from '@/core/stores/alert'
import { b64toBlob } from '@/helpers/imageUtils'
import VueCropper from 'vue-cropperjs'
import 'cropperjs/dist/cropper.css'
import FormFieldTemplate from './FormFieldTemplate.vue'
import ImageElement from '../ImageElement.vue'

const props = defineProps({
    label:         { type: String,           required: true },
    hint:          { type: String,           default: '' },
    name:          { type: String,           required: true },
    onChange:      { type: Function,         required: true },
    classname:     { type: String,           default: '' },
    required:      { type: Boolean,          default: false },
    labelStyle:    { type: Object },
    id:            { type: [String, Number], default: 'text-field' },
    value:         { type: [Object, String], default: '' },
    componentName: { type: String,           required: true },
    is_default:    { type: [Boolean, Number], default: false },
    btnName:       { type: String,           default: '' },
    buttonName:    { type: String,           default: '' },
    labelCss:      { type: Object,           default: () => ({}) },
    allowedTypes:  { type: Array,            default: () => ['image/png', 'image/jpg', 'image/jpeg'] },
    placeholderType: { type: String,         default: 'generic' },
})

const alertStore = useAlertStore()

const fileInput = ref(null)
const cropper = ref(null)

const selectedFile = ref(props.value)
const previewUrl   = ref(props.value)
const imageSrc = ref('')
const tooltip = ref('')
const styleObj = ref({ background: 'none' })
const showModal = ref(false)
const crop = ref({ width: '400px', height: '300px' })
const cropImg = ref('')
const resultImage = ref('')
const aspectRatio = ref(0)

onBeforeMount(() => {
    selectedFile.value = props.value
    previewUrl.value   = props.value
    tooltipValue(selectedFile.value)
})

watch(() => props.value, (newVal) => {
    selectedFile.value = newVal
    previewUrl.value   = newVal
})

function onFileSelected(event) {
    const file = event.target.files[0]
    if (!file) return

    if (!file.type.includes('image/')) {
        showCustomAlert(lang('select_image'))
        return
    }
    if (!props.allowedTypes.some(t => file.type.includes(t))) {
        showCustomAlert(lang('restricted_image_file'))
        return
    }
    if (file.size >= 2097152) {
        showCustomAlert(lang('max_size_new_added'))
        return
    }

    selectedFile.value = file
    event.target.value = ''

    const reader = new FileReader()
    reader.onload = async (e) => {
        imageSrc.value = e.target.result
        showModal.value = true
        await nextTick()
        cropper.value?.replace(e.target.result)
    }
    reader.readAsDataURL(file)
}

function cropImage() {
    if (cropper.value) {
        cropImg.value = cropper.value.getCroppedCanvas().toDataURL()
    }
}

function rotateImage() {
    cropper.value?.rotate(90)
}

function onClose() {
    showModal.value = false
}

function onSubmit() {
    // Always read the canvas at submit time so we get the image even if
    // the @crop event never fired (e.g. user didn't move the crop box).
    if (cropper.value) {
        cropImg.value = cropper.value.getCroppedCanvas()?.toDataURL() ?? ''
    }

    if (!cropImg.value) {
        onClose()
        return
    }

    const block       = cropImg.value.split(';')
    const contentType = block[0].split(':')[1]
    const realData    = block[1].split(',')[1]
    resultImage.value = b64toBlob(realData, contentType)

    previewUrl.value = cropImg.value

    props.onChange({
        name:  selectedFile.value.name,
        image: cropImg.value,
        file:  resultImage.value,
    }, props.name)

    onClose()
}

function tooltipValue(file) {
    tooltip.value = file !== null && file?.name === undefined
        ? file.split('/')[file.split('/').length - 1]
        : file ? file.name : 'no_file'
    styleObj.value.background = tooltip.value === 'logo.png' ? 'black' : 'none'
}

function showCustomAlert(message) {
    alertStore.setAlert({ type: 'danger', message, component_name: props.componentName })
}

function changeRatio(value) {
    cropper.value?.setAspectRatio(value)
    aspectRatio.value = value
}
</script>

<style scoped>
.hidden-input { display: none; }
.img-click { width: 100px !important; height: 100px !important; }
.profile-user-img { border: 3px solid #adb5bd; margin: 0 auto; padding: 3px; width: 100px; }
.img-rounder { border-radius: 50% !important; }

.image-container {
    width: min-content;
    margin: auto;
    position: relative !important;
    overflow: hidden;
    border-radius: 50%;
    cursor: pointer;
}

.camera_logo {
    position: absolute;
    opacity: 0;
    padding: .2rem;
    left: 50%;
    bottom: 0;
    right: 0;
    width: 100%;
    background-color: rgba(0, 0, 0, 0.32);
    transition: opacity .2s ease-in-out;
    text-align: center;
}

.image-container:hover .camera_logo {
    transition: all 0.3s ease-in-out;
    opacity: 1;
}

.translateCameraIcon {
    transform: translate(-50%);
}

.rotate-button { text-align: center; }
#rotate { margin-top: 15px; margin-bottom: 5px; }
#rotate, #ratio { border-radius: 5px; }
.rotate-button button.active { background-color: #007bff; color: #fff; }
</style>
