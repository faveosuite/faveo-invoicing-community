<template>

  <transition v-if="currentStatus" name="modal">

    <div class="modal-mask" :class="{rtl : language === 'ar'}">

      <div class="modal-wrapper" :class="classname">

        <div class="modal-content" :style="containerStyle">

          <div class="modal-header">

            <slot name="title"></slot>

            <button v-if="showCloseBtn" type="button" @click="onClose()" aria-label="Close" data-bs-dismiss="modal" class="btn-close mb-3">

            </button>

          </div>

          <div class="modal-body" :class="modalBodyClass">

            <slot name="alert"></slot>

            <slot name="fields"></slot>

          </div>

          <div v-if="showFooter" class="modal-footer">

            <slot name="controls"></slot>

          </div>
        </div>
      </div>
    </div>
  </transition>
</template>
<script>
  export default {

    name: "modal",

    description: "Modal popup Component",

    props: {

      showModal: { type: Boolean, default: false },

      onClose: { type: Function },

      containerStyle: { type: Object },

      classname: { type: String, default: "modal-md" },

      language: { type: String, default: '' },

      modalBodyClass: { type: String, default: '' },

      showCloseBtn: { type: Boolean, default: true },

      showFooter: { type: Boolean, default: true }
    },

      data() {
          return {
              currentStatus: this.showModal,

              /**
               * for rtl support
               * @type {String}
               */
          };
      },
  };
</script>

<style>
  .modal-mask {
    position: fixed;
    z-index: 1050;
    /* changed to make tinymce dialogs work*/
    top: 0;
    left: 0;
    right: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    display: table;
    font-weight: 400;
    font-size: 14px;
    /*transition: opacity 0.5s ease !important;*/
  }

  .mod_width {
      scrollbar-width: none;
  }

  .modal-wrapper {
    display: table-cell;
    vertical-align: middle;
  }

  .modal-content {
    width: 800px;
    max-width: 840px !important;
    margin: 0px auto;
    background-color: #fff;
    border-radius: 0.25rem;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.33);
    transition: all 0.5s ease !important;
    color: #444 !important;
  }

  .modal-title {
    font-weight: 400 !important;
  }

  .modal-default-button {
    float: right;
  }

  .modal-body-spacing {
    padding: 0px;
    margin-top: 1rem;
    margin-bottom: 1rem;
  }

  .modal-body{
    padding: 1.5rem;
  }

  .modal-enter .modal-container,
  .modal-leave-active .modal-container {
    -webkit-transform: scale(1.1);
    transform: scale(1.1);
  }

  #modal_close {
    font-size: 1.5rem !important;
  }

  .body-scrollable {
    max-height: 500px;
    overflow-y: auto;
  }

  .modal-header {
      display: flex;
      flex-shrink: 0;
      align-items: center;
      padding: 1rem;
      border-bottom: 1px solid #e9ecef;
      border-top-left-radius: calc(0.3rem - 1px);
      border-top-right-radius: calc(0.3rem - 1px);
  }

  .modal-header .btn-close {
        padding: 0.5rem 0.5rem;
        margin: -0.5rem -0.5rem -0.5rem auto;
        cursor: pointer;
        background-color: transparent;
        border: 0;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        font-size: xx-small;
        font-weight: bold;
  }

  .modal-footer{
    padding: 1rem;
    border-top: 1px solid #e9ecef;
    border-bottom-left-radius: calc(0.3rem - 1px);
    border-bottom-right-radius: calc(0.3rem - 1px);
  }

  .modal-header h4 {
    margin-top: 0px !important;
  }

  #crop_action{
      z-index: 1;
  }

  @media only screen and (max-width: 600px) {
    .modal-container {
      width: auto !important;
      margin: 10px !important;
    }
  }
</style>
