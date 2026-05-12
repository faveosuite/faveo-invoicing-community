<template>
    <FormFieldTemplate :label="label" :labelStyle="labelStyle" :name="name" :classname="classname"
                       :hint="hint" :required="required" :showNewButton="showNewButton"
                       :newBtnName="newBtnName" :onClickEvent="getActionEvent">
        <div v-if="type === 'textarea'">
            <textarea :id="id ? id : 'text-field-' + name" :name="name"
                      :class="['form-control', inputClass]"
                      :maxlength="length" :type="type"
                      v-model="changedValue" @input="onChange(changedValue, name)"
                      :cols="columns" :rows="rows" :style="inputStyle"
                      :placeholder="placehold">
            </textarea>
        </div>
        <div v-else-if="type === 'password'">
            <div class="password-input">
                <input :id="id ? id : 'text-field-' + name" :name="name"
                       :class="['form-control', inputClass]"
                       :type="showPassword ? 'text' : 'password'"
                       :disabled="disabled" :style="inputStyle"
                       v-model="changedValue" @input="onChange(changedValue, name)"
                       @keyup="keyupListener($event, name)" @keydown="keydownListener($event, name)"
                       @keypress="keypressEvt($event, name)" @paste="pasteEvt($event, name)"
                       :placeholder="placehold" :maxlength="max || undefined" />
                <i class="eye-icon fa" :class="!showPassword ? 'fa-eye-slash' : 'fa-eye'"
                   @click="togglePasswordVisibility"></i>
            </div>
        </div>
        <div v-else>
            <input :id="id ? id : 'text-field-' + name" :name="name"
                   :class="['form-control', inputClass]"
                   :type="type" :disabled="disabled" :style="inputStyle"
                   v-model="changedValue" @input="onChange(changedValue, name)"
                   @keyup="keyupListener($event, name)" @keydown="keydownListener($event, name)"
                   @keypress="keypressEvt($event, name)" @paste="pasteEvt($event, name)"
                   :placeholder="placehold" :maxlength="max || undefined" />
        </div>
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import FormFieldTemplate from './FormFieldTemplate.vue'

const props = defineProps({
    label:           { type: String,           default: '' },
    hint:            { type: String,           default: '' },
    value:           { type: [String, null],   required: true },
    name:            { type: String,           required: true },
    type:            { type: String,           default: 'text' },
    onChange:        { type: Function,         required: true },
    classname:       { type: String,           default: '' },
    required:        { type: Boolean,          default: false },
    length:          { type: [Number, String], default: 2000 },
    keyupListener:   { type: Function,         default: () => {} },
    keydownListener: { type: Function,         default: () => {} },
    keypressEvt:     { type: Function,         default: () => {} },
    pasteEvt:        { type: Function,         default: () => {} },
    labelStyle:      { type: Object },
    placehold:       { type: String,           default: 'Enter a value' },
    id:              { type: [String, Number], default: '' },
    disabled:        { type: Boolean,          default: false },
    columns:         { type: [String, Number], default: '' },
    inputStyle:      { type: Object,           default: () => ({}) },
    max:             { type: [Number, String], default: '' },
    rows:            { type: [Number, String], default: '' },
    cols:            { type: [Number, String], default: '' },
    inputClass:      { type: String,           default: '' },
    showWordLimit:   { type: Boolean,          default: false },
    showNewButton:   { type: Boolean,          default: false },
    newBtnName:      { type: String,           default: '' },
    onNewButtonClick: { type: Function,        default: () => {} },
})

const changedValue = ref(props.value)
const showPassword = ref(false)

onMounted(() => {
    changedValue.value = props.value
})

watch(() => props.value, (newVal) => {
    changedValue.value = newVal
})

function getActionEvent(name) {
    props.onNewButtonClick(name)
}

function togglePasswordVisibility() {
    showPassword.value = !showPassword.value
}
</script>

<style>
.eye-icon {
    position: absolute;
    top: 50%;
    right: 10px;
    transform: translateY(-50%);
    cursor: pointer;
    z-index: 2;
}
.password-input { position: relative; }
input[type="password"]::-ms-reveal { display: none; }
</style>
