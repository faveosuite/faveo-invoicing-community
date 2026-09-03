<template>
    <teleport to="body">
        <template v-if="showModal">
            <!-- Porto/Bootstrap 5 backdrop -->
            <div class="modal-backdrop fade show"></div>

            <!-- Porto/Bootstrap 5 modal -->
            <div class="modal d-block" tabindex="-1" role="dialog" @click.self="handleBackdropClick">
                <div class="modal-dialog modal-dialog-centered" :class="classname" role="document">
                    <div class="modal-content" :style="containerStyle">

                        <div class="modal-header">
                            <slot name="title"></slot>
                            <button type="button" class="btn-close" @click="onClose" aria-label="Close"></button>
                        </div>

                        <div class="modal-body" :class="modalBodyClass">
                            <slot name="fields"></slot>
                        </div>

                        <div v-if="showControls" class="modal-footer"
                             :class="[showCloseBtn ? 'justify-content-between' : 'justify-content-end', footerClass]">
                            <button v-if="showCloseBtn" type="button" class="btn btn-light" @click="onClose">
                                {{ closeLabel || 'Close' }}
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
import { watch } from 'vue'

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

watch(() => props.showModal, (val) => {
    document.body.style.overflow = val ? 'hidden' : ''
}, { immediate: true })

function handleBackdropClick() {
    if (props.closeOnBackdrop) props.onClose()
}
</script>
