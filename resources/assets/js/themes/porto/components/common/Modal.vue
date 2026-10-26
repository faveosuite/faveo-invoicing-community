<template>
    <teleport to="body">
        <template v-if="showModal">
            <!-- Porto/Bootstrap 5 backdrop -->
            <div class="modal-backdrop fade show"></div>

            <!-- Porto/Bootstrap 5 modal -->
            <div ref="modalEl" class="modal d-block" tabindex="-1" role="dialog" aria-modal="true"
                 :aria-labelledby="titleId"
                 @click.self="handleBackdropClick"
                 @keydown.esc.stop="onClose"
                 @keydown.tab="trapTab">
                <div class="modal-dialog modal-dialog-centered" :class="classname" role="document">
                    <div class="modal-content" :style="containerStyle">

                        <div class="modal-header">
                            <div :id="titleId" class="flex-grow-1"><slot name="title"></slot></div>
                            <button type="button" class="btn-close" @click="onClose" :aria-label="__('message.close')"></button>
                        </div>

                        <div class="modal-body" :class="modalBodyClass">
                            <slot name="fields"></slot>
                        </div>

                        <div v-if="showControls" class="modal-footer"
                             :class="[showCloseBtn ? 'justify-content-between' : 'justify-content-end', footerClass]">
                            <button v-if="showCloseBtn" type="button" class="btn btn-light" @click="onClose">
                                {{ closeLabel || __('message.close') }}
                            </button>
                            <slot name="controls"></slot>
                        </div>

                    </div>
                </div>
            </div>
        </template>
    </teleport>
</template>

<script setup>
import { watch, ref, nextTick, onBeforeUnmount, useId } from 'vue'

const props = defineProps({
    showModal:       { type: Boolean,  default: false },
    onClose:         { type: Function, default: () => {} },
    containerStyle:  { type: Object,   default: () => ({}) },
    classname:       { type: String,   default: '' },
    modalBodyClass:  { type: String,   default: '' },
    closeLabel:      { type: String,   default: '' },
    showCloseBtn:    { type: Boolean,  default: true },
    showControls:    { type: Boolean,  default: true },
    footerClass:     { type: String,   default: '' },
    closeOnBackdrop: { type: Boolean,  default: false },
})

const titleId = `modal-title-${useId()}`
const modalEl = ref(null)
let lastFocused = null

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'
const focusables = () => [...(modalEl.value?.querySelectorAll(FOCUSABLE) ?? [])].filter(el => el.offsetParent !== null)

watch(() => props.showModal, async (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
    if (val) {
        lastFocused = document.activeElement
        await nextTick()
        // Focus the dialog itself, not its first field: focusing a select
        // (vue-select) pops its list open, and on phones a field pops the keyboard.
        modalEl.value?.focus()
    } else {
        restoreFocus()
    }
}, { immediate: true })

onBeforeUnmount(() => {
    if (props.showModal) {
        document.body.style.overflow = ''
        restoreFocus()
    }
})

function restoreFocus() {
    if (lastFocused?.isConnected) lastFocused.focus()
    lastFocused = null
}

function trapTab(e) {
    const els = focusables()
    if (!els.length) return
    const first = els[0], last = els[els.length - 1]
    const onDialog = document.activeElement === modalEl.value
    if (e.shiftKey && (document.activeElement === first || onDialog)) { e.preventDefault(); last.focus() }
    else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus() }
}

function handleBackdropClick() {
    if (props.closeOnBackdrop) props.onClose()
}
</script>

<style scoped>
.modal { overscroll-behavior: contain; }
</style>
