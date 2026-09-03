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
import { asset } from '@/core/utils/asset.js'

const props = defineProps({
    id:              { type: String,           required: true },
    sourceUrl:       { type: String,           required: true },
    styleObject:     { type: Object,           default: () => ({}) },
    classes:         { type: Array,            default: () => [] },
    alternativeText: { type: String,           default: '' },
    imgWidth:        { type: [Number, String], default: 'auto' },
    imgHeight:       { type: [Number, String], default: 'auto' },
    // 'generic' (default) is a neutral "no photo" icon, used for logos/SEO
    // images. 'avatar' shows the person-silhouette used for profile pictures.
    placeholderType: { type: String,           default: 'generic' },
})

// Self-contained "no image" placeholder — an inline SVG data URI rather than
// a static asset path, so it can never 404 (which previously caused an
// infinite @error retry loop when the fallback file itself was missing).
const GENERIC_PLACEHOLDER = 'data:image/svg+xml;utf8,' + encodeURIComponent(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 120">'
    + '<rect width="120" height="120" fill="#EFF1F3"/>'
    + '<path fill-rule="evenodd" clip-rule="evenodd" fill="#687787" d="M33.2503 38.4816C33.2603 37.0472 34.4199 35.8864 35.8543 35.875H83.1463C84.5848 35.875 85.7503 37.0431 85.7503 38.4816V80.5184C85.7403 81.9528 84.5807 83.1136 83.1463 83.125H35.8543C34.4158 83.1236 33.2503 81.957 33.2503 80.5184V38.4816ZM80.5006 41.1251H38.5006V77.8751L62.8921 53.4783C63.9172 52.4536 65.5788 52.4536 66.6039 53.4783L80.5006 67.4013V41.1251ZM43.75 51.6249C43.75 54.5244 46.1005 56.8749 49 56.8749C51.8995 56.8749 54.25 54.5244 54.25 51.6249C54.25 48.7254 51.8995 46.3749 49 46.3749C46.1005 46.3749 43.75 48.7254 43.75 51.6249Z"/>'
    + '</svg>'
)

// Real static file (like favMer's contacthead.png) rather than an inline SVG,
// so it matches the person-avatar used in Navbar/UserShow. Still can't loop:
// onImageLoadError bails out once the placeholder is already showing.
const AVATAR_PLACEHOLDER = asset('images/avatar.png')

const placeholderImage = computed(() => (
    props.placeholderType === 'avatar' ? AVATAR_PLACEHOLDER : GENERIC_PLACEHOLDER
))

const getSrc = computed(() => props.sourceUrl || placeholderImage.value)

function onImageLoadError(event) {
    // Already on the placeholder — stop here instead of re-setting the same
    // src, which would just re-trigger @error forever.
    if (event.target.src === placeholderImage.value) return
    event.target.src = placeholderImage.value
}
</script>

<style scoped>
img { object-fit: contain; }
.img-circle { border-radius: 50%; }
.profile-user-img { border: 3px solid #adb5bd; margin: 0 auto; padding: 3px; width: 100px; }
.img-rounder { border-radius: 50% !important; }
.img-click { width: 100px !important; height: 100px !important; }
</style>
