import { defineStore } from 'pinia'
import { useDateTimeStore } from './dateTimeStore'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user_data: '',
        api_key: '',
        admin_data: '',
        clientTimezone: '',
    }),
    actions: {
        setUserInfo(payload) {
            this.user_data = payload
            const tz = payload?.timezone?.name || payload?.client_timezone?.name || null
            useDateTimeStore().setUserTimezone(tz)
        },
        setUserData(payload) {
            if (this.user_data) {
                this.user_data.client_profile_pic = payload.profile_pic
                this.user_data.client_mobile_code = payload.client_mobile_code
                this.user_data.client_iso2 = payload.client_iso2
                this.user_data.client_fname = payload.client_fname
                this.user_data.client_lname = payload.client_lname
                this.user_data.client_email = payload.client_email
                this.user_data.client_timezone_id = payload.client_timezone_id
            }
        },
        setApiKey(key = '') {
            this.api_key = key
        },
        setAdminData(payload) {
            this.admin_data = payload
        },
        setClientTimezone(timezone) {
            this.clientTimezone = timezone
            useDateTimeStore().setUserTimezone(timezone?.name || null)
        },
    },
})
