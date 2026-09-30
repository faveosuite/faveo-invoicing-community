import { defineStore } from 'pinia'

export const useAlertStore = defineStore('alert', {
    state: () => ({
        message: '',
        type: '',
        component_name: '',
        duration: '',
    }),
    actions: {
        setAlert({ message, type, component_name, duration = '' }) {
            this.message = message
            this.type = type
            this.component_name = component_name
            this.duration = duration
        },
        unsetAlert() {
            this.message = ''
            this.type = ''
            this.component_name = ''
            this.duration = ''
        },
    },
})
