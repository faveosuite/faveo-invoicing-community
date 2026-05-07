<template>
    <teleport to="body">
        <div class="modal-backdrop fade show"></div>
        <div class="modal d-block" tabindex="-1" @mousedown.self="onClose">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">

                    <div class="modal-header">
                        <h5 class="modal-title">{{ modalTitle ? trans(modalTitle) : trans('delte') }}</h5>
                        <button type="button" class="btn-close" @click="onClose"></button>
                    </div>

                    <div class="modal-body">
                        <AppAlert componentName="delete-modal" />
                        <div v-if="loading" class="d-flex justify-content-center my-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                        </div>
                        <p v-if="!loading" class="mb-0">{{ modalMessage ? trans(modalMessage) : trans('are_you_sure') }}</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="onClose" :disabled="isDisabled">
                            {{ trans('cancel') }}
                        </button>
                        <button type="button" class="btn btn-danger" @click="onSubmit()" :disabled="isDisabled">
                            <i :class="btnTitle === 'restore' ? 'fas fa-sync-alt' : 'fas fa-trash'" class="me-1"></i>
                            {{ btnTitle ? trans(btnTitle) : trans('delte') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </teleport>
</template>

<script type="text/javascript">

    import axios from '@/plugins/axios';
    import { errorHandler, successHandler } from '../../helpers/responseHandler'

    export default {

        name : 'delete-modal',

        props:{

            showModal:      { type: Boolean, default: false },
            deleteUrl:      { type: String },
            onClose:        { type: Function },
            alertComponentName: { type: String, default: 'dataTableModal' },
            redirectUrl:    { type: String, default: '' },
            modalTitle:     { type: String, default: '' },
            modalMessage:   { type: String, default: '' },
            btnTitle:       { type: String, default: '' },
            componentTitle: { type: String, default: '' },
            keyVal:         { type: String, default: '' },
            idVal:          { type: [String, Number], default: '' },
            softDelete:     { type: Boolean, default: false },
        },

        data () {
            return {
                loading: false,
                isDisabled: false,
                apiUrl: this.deleteUrl
            }
        },

        methods: {

            onSubmit() {
                this.loading = true
                this.isDisabled = true

                const data = {}
                data[this.keyVal] = this.idVal

                if (this.softDelete) {
                    data['soft_delete'] = 0
                }

                axios.post(this.apiUrl, data).then(res => {
                    successHandler(res, this.alertComponentName)
                    this.afterRespond()
                }).catch(err => {
                    errorHandler(err, 'delete-modal')
                    this.loading = false
                    this.isDisabled = false
                })
            },

            afterRespond() {
                if (this.redirectUrl) {
                    setTimeout(() => {
                        this.$router.push({ path: this.redirectUrl })
                    }, 3000)
                } else {
                    window.emitter.emit('refreshData')
                }

                this.onClose()
                this.loading = false
                this.isDisabled = false
            }
        }
    };
</script>
