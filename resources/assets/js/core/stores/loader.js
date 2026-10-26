import { defineStore } from 'pinia'

export const useLoaderStore = defineStore('loader', {
    state: () => ({
        axiosCallsStack: []
    }),
    getters: {
        showLoader: state => state.axiosCallsStack.length > 0
    },
    actions: {
        startLoader(loaderInstance) {
            if (!loaderInstance) throw new Error('Loader instance is required!')
            if (this.axiosCallsStack.includes(loaderInstance)) return
            this.axiosCallsStack.push(loaderInstance)
        },
        stopLoader(loaderInstance) {
            if (!loaderInstance) throw new Error('Loader instance is required!')
            const index = this.axiosCallsStack.indexOf(loaderInstance)
            if (index === -1) return
            this.axiosCallsStack.splice(index, 1)
        },
        forceStopLoader() {
            this.axiosCallsStack = []
        }
    }
})
