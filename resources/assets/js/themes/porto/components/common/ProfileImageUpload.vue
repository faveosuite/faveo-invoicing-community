<template>
    <div class="client-avatar-wrapper" @click="fileInput.click()" title="Change profile picture">
        <div class="client-avatar-circle">
            <img v-if="currentPreview" :src="currentPreview" :alt="alt" class="client-avatar-img">
            <span v-else class="client-avatar-initials">{{ initials }}</span>
        </div>
        <div class="client-avatar-overlay">
            <i class="fas fa-camera text-white"></i>
        </div>
    </div>
    <input ref="fileInput" type="file" accept="image/png,image/jpeg,image/jpg" class="hidden-input" @change="onFileSelected">

    <AppModal :showModal="showModal" :onClose="closeModal" :showCloseBtn="false" classname="modal-lg">
        <template #title>
            <h4 class="modal-title">Crop Profile Picture</h4>
        </template>
        <template #fields>
            <vue-cropper
                ref="cropper"
                :guides="true"
                :view-mode="2"
                drag-mode="crop"
                :auto-crop="true"
                :background="true"
                :rotatable="true"
                :min-container-height="300"
                :src="imageSrc"
                :aspect-ratio="aspectRatio"
                @crop="onCrop"
            />
            <div class="text-center mt-2 d-flex justify-content-center gap-1 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="rotateImage">
                    <i class="fas fa-sync-alt me-1"></i>Rotate
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" :class="{ active: aspectRatio === 0 }" @click="changeRatio(0)">
                    No Ratio
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" :class="{ active: aspectRatio === 1 }" @click="changeRatio(1)">
                    1:1 Ratio
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" :class="{ active: aspectRatio === 16/9 }" @click="changeRatio(16/9)">
                    16:9 Ratio
                </button>
            </div>
        </template>
        <template #controls>
            <button type="button" class="btn btn-light me-2" @click="closeModal">Cancel</button>
            <button type="button" class="btn btn-primary" @click="onSubmit">
                <i class="fa fa-check me-1"></i>Apply
            </button>
        </template>
    </AppModal>
</template>

<script setup>
import { ref, nextTick } from 'vue'
import { b64toBlob } from '@/helpers/imageUtils'
import VueCropperImport from 'vue-cropperjs'
import 'cropperjs/dist/cropper.css'

// vue-cropperjs ships as a Babel-transpiled CJS module (exports.default = {...}),
// which Vite doesn't always unwrap — import can resolve to the raw
// { default, __esModule } wrapper instead of the component itself.
const VueCropper = VueCropperImport?.default ?? VueCropperImport

const props = defineProps({
    src:      { type: String, default: '' },
    initials: { type: String, default: '?' },
    alt:      { type: String, default: 'Avatar' },
})

const emit = defineEmits(['change'])

const fileInput      = ref(null)
const cropper        = ref(null)
const showModal      = ref(false)
const imageSrc       = ref('')
const cropImg        = ref('')
const currentPreview = ref(props.src)
const aspectRatio    = ref(1)

async function onFileSelected(event) {
    const file = event.target.files[0]
    if (!file) return

    if (!['image/png', 'image/jpg', 'image/jpeg'].includes(file.type)) {
        alert('Only PNG and JPEG images are allowed.')
        return
    }
    if (file.size > 2097152) {
        alert('Image must be under 2 MB.')
        return
    }

    event.target.value = ''

    const reader = new FileReader()
    reader.onload = async (e) => {
        imageSrc.value  = e.target.result
        showModal.value = true
        await nextTick()
        cropper.value?.replace(e.target.result)
    }
    reader.readAsDataURL(file)
}

function onCrop() {
    if (cropper.value) {
        cropImg.value = cropper.value.getCroppedCanvas().toDataURL()
    }
}

function rotateImage() {
    cropper.value?.rotate(90)
}

function changeRatio(value) {
    aspectRatio.value = value
    cropper.value?.setAspectRatio(value)
}

function closeModal() {
    showModal.value = false
}

function onSubmit() {
    if (cropper.value) {
        cropImg.value = cropper.value.getCroppedCanvas()?.toDataURL() ?? ''
    }
    if (!cropImg.value) { closeModal(); return }

    const block       = cropImg.value.split(';')
    const contentType = block[0].split(':')[1]
    const realData    = block[1].split(',')[1]
    const blob        = b64toBlob(realData, contentType)

    currentPreview.value = cropImg.value
    emit('change', { file: blob, previewUrl: cropImg.value })
    closeModal()
}

</script>

<style scoped>
.hidden-input { display: none; }
.client-avatar-wrapper {
    position: relative;
    width: 110px;
    height: 110px;
    margin: 0 auto;
    cursor: pointer;
    border-radius: 50%;
    overflow: hidden;
}

.client-avatar-circle {
    width: 110px;
    height: 110px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #adb5bd;
    padding: 3px;
    background: #fff;
}

.client-avatar-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.client-avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background: #e9ecef;
    border-radius: 50%;
    color: #6c757d;
    font-weight: bold;
    font-size: 2rem;
}

.client-avatar-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 36px;
    background: rgba(0, 0, 0, .4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity .2s ease-in-out;
}

.client-avatar-wrapper:hover .client-avatar-overlay {
    opacity: 1;
}
</style>
