<template>
    <editor
        :tinymce-script-src="tinymceSrc"
        api-key="no-api-key"
        licenseKey="gpl"
        :id="id"
        :init="editorInit"
        v-model="editorValue"
    />
</template>

<script setup>
import { ref, watch } from 'vue'
import Editor from '@tinymce/tinymce-vue'
import { editorInit } from './tinyMceDefaults.js'

const el = document.getElementById('app-root')
const baseUrl = el?.dataset?.baseUrl ?? ''
const tinymceSrc = `${baseUrl}/themes/default/common/tinymce/js/tinymce/tinymce.min.js`

const props = defineProps({
    name: { type: String, required: true },
    value: { type: String, default: '' },
    onChange: { type: Function, required: true },
    id:   { type: String, default: 'tiny_editor' },
})

const editorValue = ref(props.value)

watch(() => props.value, (val) => {
    if (val !== editorValue.value) editorValue.value = val
})

watch(editorValue, (val) => {
    props.onChange(val, props.name)
})
</script>
