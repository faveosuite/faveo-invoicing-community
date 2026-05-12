<template>
    <div class="mb-3"
         :class="[classname, 'form-group', 'form-field-template', { 'has-error': name in errors }, { 'row': isInlineForm }]"
         :id="label">

        <div :class="{ 'col-md-2 flex break': isInlineForm }">
            <label class="form-label" :for="label" :style="labelStyle">{{ label }}</label>
            <label class="is-danger" :style="labelStyle" v-if="required">*</label>
            <ToolTip v-if="hint !== '' && !tipRule" :message="getHint(hint)" size="small" />
            <slot name="word-limit-counter"></slot>
            <i v-if="isClearField && value && typeof value === 'object'" @click="clearField"
               class="fas fa-times clear-btn" title="Clear" aria-hidden="true"></i>
            <a v-if="showNewButton" class="btn btn-light mb-2 float-end btn-xs pt-0 pb-0"
               href="javascript:;" @click="clickEvent(name)">
                <i class="fas fa-plus plus-icon"></i> {{ lang(newBtnName) }}
            </a>
            <i class="float-end" v-if="showPreview">(e.g {{ showPreview }})</i>
        </div>

        <div :class="[isInlineForm ? 'col-md-10 flex' : '']">
            <div class="slot-container">
                <slot></slot>
                <template v-if="hint !== '' && tipRule">
                    <div class="text-small">
                        <i class="fas fa-question-circle text-primary"></i>
                        <em v-html="getHint(hint)"></em>
                    </div>
                </template>
                <div v-if="name in errors" class="error-block is-danger">{{ errors[name] }}</div>
            </div>
            <button v-if="actionBtn" class="btn btn-light form-field-action-button"
                    @click="() => actionBtn.action()">
                <span>{{ lang(actionBtn.text) }}</span>
            </button>
        </div>
    </div>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useAlertStore } from '@/core/stores/alert'
import { lang } from '@/helpers/extraLogics'
import ToolTip from '../Tooltip.vue'

const props = defineProps({
    label:         { type: String,                               required: true },
    name:          { type: [String, Number],                     required: true },
    labelStyle:    { type: Object,                               default: () => ({}) },
    classname:     { type: String,                               default: '' },
    hint:          { type: String,                               default: '' },
    required:      { type: Boolean,                              default: false },
    isClearField:  { type: Boolean,                              default: false },
    clearField:    { type: Function,                             default: () => {} },
    value:         { type: [String, Date, Object, Array],        default: '' },
    showNewButton: { type: Boolean,                              default: false },
    onClickEvent:  { type: Function,                             default: () => {} },
    isInlineForm:  { type: Boolean,                              default: false },
    actionBtn:     { type: Object,                               default: () => null },
    showPreview:   { type: [String, Object],                     default: '' },
    tipRule:       { type: [Number, Boolean],                    default: false },
    newBtnName:    { type: String,                               default: '' },
})

const alertStore = useAlertStore()

const errors = computed(() => alertStore.validation_errors)

watch(errors, () => {
    setTimeout(() => {
        const errorBlock = document.querySelectorAll('.error-block:not([style*="display: none"])')[0]
        errorBlock?.scrollIntoView({ behavior: 'smooth', block: 'center' })
    }, 1)
})

function clickEvent(name) {
    props.onClickEvent(name)
}

function getHint(value) {
    return value.replace(/\n/g, '<br>')
}
</script>

<style scoped>
.slot-container { width: inherit; }
.form-field-action-button { height: fit-content; white-space: nowrap; }
.plus-icon { font-size: 0.9rem; font-weight: 900 !important; }
</style>
