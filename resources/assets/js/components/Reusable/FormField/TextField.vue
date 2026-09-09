<template>
    <FormFieldTemplate :label="label" :labelStyle="labelStyle" :name="name" :classname="classname"
                       :hint="hint" :required="required" :showNewButton="showNewButton"
                       :newBtnName="newBtnName" :onClickEvent="getActionEvent"
                       :inputGroupBtn="inputGroupBtn" :error="error">
        <div v-if="type === 'textarea'">
            <textarea :id="id ? id : 'text-field-' + name" :name="name"
                      :class="['form-control', inputClass, { 'is-invalid': error }]"
                      :maxlength="length" :type="type"
                      v-model="changedValue" @input="onChange(changedValue, name)"
                      :cols="columns" :rows="rows" :style="inputStyle"
                      :placeholder="placeholderVal"
                      :required="required">
            </textarea>
        </div>
        <div v-else-if="type === 'password'">
            <div class="input-group">
                <input :id="id ? id : 'text-field-' + name" :name="name"
                       :class="['form-control', inputClass, { 'is-invalid': error }]"
                       :type="showPassword ? 'text' : 'password'"
                       :disabled="disabled" :readonly="readonly" :style="inputStyle"
                       v-model="changedValue" @input="onChange(changedValue, name)"
                       @keyup="keyupListener($event, name)" @keydown="keydownListener($event, name)"
                       @keypress="keypressEvt($event, name)" @paste="pasteEvt($event, name)"
                       :placeholder="placeholderVal" :maxlength="max || undefined"
                       :required="required" />
                <span class="input-group-text cursor-pointer" @click="togglePasswordVisibility">
                    <i class="fa" :class="showPassword ? 'fa-eye' : 'fa-eye-slash'"></i>
                </span>
            </div>
        </div>
        <div v-else-if="suffix" class="input-group">
            <input :id="id ? id : 'text-field-' + name" :name="name"
                   :class="['form-control', inputClass, { 'is-invalid': error }]"
                   :type="type" :disabled="disabled" :readonly="readonly" :style="inputStyle"
                   v-model="changedValue" @input="onChange(changedValue, name)"
                   @keyup="keyupListener($event, name)" @keydown="keydownListener($event, name)"
                   @keypress="keypressEvt($event, name)" @paste="pasteEvt($event, name)"
                   :placeholder="placeholderVal" :maxlength="max || undefined"
                   :required="required" />
            <span class="input-group-text">{{ suffix }}</span>
        </div>
        <input v-else :id="id ? id : 'text-field-' + name" :name="name"
               :class="['form-control', inputClass, { 'is-invalid': error }]"
               :type="type" :disabled="disabled" :readonly="readonly" :style="inputStyle"
               v-model="changedValue" @input="onChange(changedValue, name)"
               @keyup="keyupListener($event, name)" @keydown="keydownListener($event, name)"
               @keypress="keypressEvt($event, name)" @paste="pasteEvt($event, name)"
               :placeholder="placeholderVal" :maxlength="max || undefined"
               :required="required" />
    </FormFieldTemplate>
</template>

<script setup>
import { ref, watch, onMounted, computed } from 'vue'
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
    placeholder:     { type: String,           default: '' },
    placehold:       { type: String,           default: '' },
    id:              { type: [String, Number], default: '' },
    disabled:        { type: Boolean,          default: false },
    readonly:        { type: Boolean,          default: false },
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
    inputGroupBtn:   { type: Object,           default: () => null },
    error:           { type: String,           default: undefined },
    // Plain text appended after the input, e.g. ".example.com" for a
    // subdomain field — matches the client panel's cloud-domain field.
    suffix:          { type: String,           default: '' },
})

const changedValue = ref(props.value)
const showPassword = ref(false)
const placeholderVal = computed(() => props.placeholder || props.placehold || '')

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
input[type="password"]::-ms-reveal { display: none; }
.cursor-pointer { cursor: pointer; }
</style>
