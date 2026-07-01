<template>
    <img
        :id="id"
        :src="getSrc"
        :class="classes"
        :alt="alternativeText"
        :width="imgWidth"
        :height="imgHeight"
        :style="styleObject"
        @error="onImageLoadError($event)"
    />
</template>

<script setup>
import { computed } from 'vue'
import { useBaseUrl } from '@/core/composables/useBaseUrl'

const props = defineProps({
    id:              { type: String,           required: true },
    sourceUrl:       { type: String,           required: true },
    styleObject:     { type: Object,           default: () => ({}) },
    classes:         { type: Array,            default: () => [] },
    alternativeText: { type: String,           default: '' },
    imgWidth:        { type: [Number, String], default: 'auto' },
    imgHeight:       { type: [Number, String], default: 'auto' },
    defaultImage:    { type: String,           default: 'default.png' },
})

const baseUrl = useBaseUrl()

const getSrc = computed(() =>
    props.sourceUrl ? props.sourceUrl : `${baseUrl}/themes/default/img/${props.defaultImage}`
)

function onImageLoadError(event) {
    event.target.onerror = null
    event.target.src = `${baseUrl}/themes/default/img/${props.defaultImage}`
}
</script>

<style scoped>
img { object-fit: contain; }
.img-circle { border-radius: 50%; }
.profile-user-img { border: 3px solid #adb5bd; margin: 0 auto; padding: 3px; width: 100px; }
.img-rounder { border-radius: 50% !important; }
.img-click { width: 100px !important; height: 100px !important; }
</style>
