import { defineStore } from 'pinia'

export const useAlertStore = defineStore('alert', {
    state: () => ({
        message: '',
        type: '',
        component_name: '',
    }),
    actions: {
        setAlert({ message, type, component_name }) {
            this.message = message
            this.type = type
            this.component_name = component_name
        },
        unsetAlert() {
            this.message = ''
            this.type = ''
            this.component_name = ''
        },
    },
})
