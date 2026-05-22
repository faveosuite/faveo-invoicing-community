<template>
    <div class="mb-3">
        <label v-if="label" class="form-label fw-bold">
            {{ label }}<span v-if="required" class="text-danger ms-1">*</span>
        </label>
        <editor
            :tinymce-script-src="tinymceSrc"
            api-key="no-api-key"
            licenseKey="gpl"
            :id="id"
            :init="editorInit"
            v-model="editorValue"
        />
        <div v-if="fieldError" class="invalid-feedback d-block mt-1">{{ fieldError }}</div>
    </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'
import Editor from '@tinymce/tinymce-vue'
import { editorInit } from './tinyMceDefaults.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const tinymceSrc = `${baseUrl}/themes/default/common/tinymce/js/tinymce/tinymce.min.js`

const props = defineProps({
    name:     { type: String, required: true },
    value:    { type: String, default: '' },
    onChange: { type: Function, required: true },
    id:       { type: String, default: 'tiny_editor' },
    label:    { type: String, default: '' },
    required: { type: Boolean, default: false },
    error:    { type: String, default: undefined },
})

const fieldError = computed(() => props.error ?? '')

const editorValue = ref(props.value)

watch(() => props.value, (val) => {
    if (val !== editorValue.value) editorValue.value = val
})

watch(editorValue, (val) => {
    props.onChange(val, props.name)
})
</script>
