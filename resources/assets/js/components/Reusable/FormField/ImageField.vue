<template>
    <div>
        <div class="d-flex align-items-center gap-3">
            <img v-if="previewUrl" :src="previewUrl" alt="" class="rounded border flex-shrink-0 image-preview" />
            <div class="flex-grow-1">
                <label :for="name" class="form-label fw-bold">
                    {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
                </label>
                <input
                    :id="name"
                    ref="fileInput"
                    type="file"
                    class="form-control"
                    accept="image/jpeg,image/png,image/jpg"
                    @change="onFileSelected"
                />
            </div>
        </div>

        <AppModal v-if="showModal" :showModal="showModal" :onClose="onClose">
            <template #title>
                <div><h4>Crop Image</h4></div>
            </template>
            <template #fields>
                <div>
                    <vue-cropper
                        ref="cropper"
                        :guides="true"
                        :view-mode="2"
                        drag-mode="crop"
                        :auto-crop="true"
                        :auto-crop-area="100"
                        :min-container-width="570"
                        :min-container-height="300"
                        :background="true"
                        :rotatable="true"
                        :src="imageSrc"
                        :img-style="crop"
                        @crop="cropImage"
                        :aspect-ratio="aspectRatio"
                    />
                </div>
                <div class="rotate-button">
                    <button v-if="imageSrc" @click="rotateImage" id="rotate">
                        <i class="fas fa-sync-alt"></i>&nbsp;{{ lang('rotate') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(0)" :class="{ active: aspectRatio === 0 }" class="m-1">
                        &nbsp;{{ lang('no_ratio') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(1)" :class="{ active: aspectRatio === 1 }" class="m-1">
                        &nbsp;{{ '1:1 ' + lang('ratio') }}
                    </button>
                    <button v-if="imageSrc" @click="changeRatio(2)" :class="{ active: aspectRatio === 2 }" class="m-1">
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
    </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'
import { lang } from '@/helpers/extraLogics'
import { useAlertStore } from '@/core/stores/alert'
import { b64toBlob } from '@/helpers/imageUtils'
import VueCropper from 'vue-cropperjs'
import 'cropperjs/dist/cropper.css'

const props = defineProps({
    label:         { type: String,   required: true },
    name:          { type: String,   required: true },
    onChange:      { type: Function, required: true },
    componentName: { type: String,   required: true },
    value:         { type: String,   default: '' },
    required:      { type: Boolean,  default: false },
})

const alertStore   = useAlertStore()
const fileInput    = ref(null)
const cropper      = ref(null)
const previewUrl   = ref(props.value || '')
const imageSrc     = ref('')
const showModal    = ref(false)
const crop         = ref({ width: '400px', height: '300px' })
const cropImg      = ref('')
const aspectRatio  = ref(0)
const selectedFile = ref(null)

watch(() => props.value, (newVal) => {
    if (newVal && !selectedFile.value) previewUrl.value = newVal
})

function onFileSelected(event) {
    const file = event.target.files[0]
    if (!file) return

    if (!file.type.includes('image/')) {
        showAlert(lang('select_image'))
        resetInput()
        return
    }
    if (!['image/png', 'image/jpg', 'image/jpeg'].some(t => file.type.includes(t))) {
        showAlert(lang('restricted_image_file'))
        resetInput()
        return
    }
    if (file.size >= 2097152) {
        showAlert(lang('max_size_new_added'))
        resetInput()
        return
    }

    selectedFile.value = file
    resetInput()

    const reader = new FileReader()
    reader.onload = async (e) => {
        imageSrc.value  = e.target.result
        showModal.value = true
        await nextTick()
        cropper.value?.replace(e.target.result)
    }
    reader.readAsDataURL(file)
}

function cropImage() {
    if (cropper.value) cropImg.value = cropper.value.getCroppedCanvas().toDataURL()
}

function rotateImage() {
    cropper.value?.rotate(90)
}

function onClose() {
    showModal.value = false
}

function onSubmit() {
    if (cropper.value) {
        cropImg.value = cropper.value.getCroppedCanvas()?.toDataURL() ?? ''
    }

    if (!cropImg.value) { onClose(); return }

    const block       = cropImg.value.split(';')
    const contentType = block[0].split(':')[1]
    const realData    = block[1].split(',')[1]
    const blob        = b64toBlob(realData, contentType)

    previewUrl.value = cropImg.value

    props.onChange({
        name:  selectedFile.value.name,
        image: cropImg.value,
        file:  blob,
    }, props.name)

    onClose()
}

function changeRatio(value) {
    cropper.value?.setAspectRatio(value)
    aspectRatio.value = value
}

function resetInput() {
    if (fileInput.value) fileInput.value.value = ''
}

function showAlert(message) {
    alertStore.setAlert({ type: 'danger', message, component_name: props.componentName })
}
</script>

<style scoped>
.image-preview { height: 80px; width: 80px; object-fit: cover; }
.rotate-button { text-align: center; }
#rotate { margin-top: 15px; margin-bottom: 5px; }
#rotate, .m-1 { border-radius: 5px; }
.rotate-button button.active { background-color: #007bff; color: #fff; }
</style>
