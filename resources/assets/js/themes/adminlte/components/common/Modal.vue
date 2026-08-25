<template>
    <transition name="modal">
        <div v-if="showModal" class="modal-mask" @keyup.esc="onClose">
            <div class="modal-wrapper" :class="classname">
                <div class="modal-content common-modal" :style="containerStyle">

                    <div class="modal-header">
                        <slot name="title"></slot>
                        <button type="button" @click="onClose" aria-label="Close"
                                class="btn-close ms-auto">
                        </button>
                    </div>

                    <div class="modal-body" :class="modalBodyClass">
                        <slot name="alert"></slot>
                        <slot name="fields"></slot>
                    </div>

                    <div v-if="showControls" class="modal-footer"
                         :class="[showCloseBtn ? 'justify-content-between' : '', footerClass]">
                        <button v-if="showCloseBtn" type="button" class="btn btn-light" @click="onClose">
                            <i class="fas fa-times"></i>&nbsp;
                            <span v-if="showbuttonName">{{ showbuttonName }}</span>
                            <span v-else>{{ __('message.close') }}</span>
                        </button>
                        <slot name="controls"></slot>
                    </div>

                </div>
            </div>
        </div>
    </transition>
</template>

<script setup>
import { watch } from 'vue'

const props = defineProps({
    showModal:      { type: Boolean,  default: false },
    onClose:        { type: Function, default: () => {} },
    containerStyle: { type: Object,   default: () => ({}) },
    classname:      { type: String,   default: 'modal-md' },
    modalBodyClass: { type: String,   default: '' },
    showbuttonName: { type: String,   default: '' },
    showCloseBtn:   { type: Boolean,  default: true },
    showControls:   { type: Boolean,  default: true },
    footerClass:    { type: String,   default: '' },
})

watch(() => props.showModal, (val) => {
    document.body.style.overflow = val ? 'hidden' : 'auto'
})
</script>

<style>
.modal-mask {
    position: fixed;
    z-index: 1050;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: table;
    font-weight: 400;
    font-size: 14px;
}

.modal-wrapper {
    display: table-cell;
    vertical-align: middle;
    padding: 0 15px;
}

.modal-content.common-modal {
    max-width: 600px;
    margin: 0 auto;
    background-color: #fff;
    border-radius: 0.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.5s ease !important;
    color: #444 !important;
}

.modal-header {
    display: flex;
    flex-shrink: 0;
    align-items: center;
    padding: 0.7rem;
    border-bottom: 1px solid #e9ecef;
    border-top-left-radius: calc(0.3rem - 1px);
    border-top-right-radius: calc(0.3rem - 1px);
}

.modal-header h4 {
    margin-top: 0 !important;
    margin-bottom: 0 !important;
    font-size: 1.2rem;
}

.modal-header .btn-close {
    padding: 0.5rem;
    cursor: pointer;
    background-color: transparent;
    border: 0;
    font-size: x-small;
    font-weight: bolder;
}

.modal-body {
    padding: 0.9rem;
}

.modal-footer {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    padding: 0.9rem;
    border-top: 1px solid #e9ecef;
    border-bottom-left-radius: calc(0.3rem - 1px);
    border-bottom-right-radius: calc(0.3rem - 1px);
}

.modal-enter-active,
.modal-leave-active { transition: opacity 0.3s ease; }
.modal-enter-from,
.modal-leave-to     { opacity: 0; }

@media (max-width: 600px) {
    .modal-content.common-modal { width: auto !important; margin: 10px !important; }
}
</style>
