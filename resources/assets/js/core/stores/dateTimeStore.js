import { defineStore } from 'pinia'
import { phpToLuxon } from '../../helpers/luxonHelpers'
import http from '../../plugins/axios'
import { useAuthStore } from './auth'

export const useDateTimeStore = defineStore('dateTime', {
    state: () => ({
        systemTimezone:  'UTC',
        userTimezone:    null,
        dateFormat:      'd/m/Y',
        timeFormat:      'H:i',
        isReady:         false,
    }),

    getters: {
        timezone: (state) => state.userTimezone || state.systemTimezone,

        luxonDateFormat: (state) => phpToLuxon(state.dateFormat),

        luxonTimeFormat: (state) => phpToLuxon(state.timeFormat),

        luxonDateTimeFormat: (state) =>
            `${phpToLuxon(state.dateFormat)} ${phpToLuxon(state.timeFormat)}`,
    },

    actions: {
        init({ timezone, dateFormat, timeFormat }) {
            this.systemTimezone = timezone  || 'UTC'
            this.dateFormat     = dateFormat || 'd/m/Y'
            this.timeFormat     = timeFormat || 'H:i'
            this.isReady        = true
        },

        setUserTimezone(timezone) {
            this.userTimezone = timezone || null
        },

        clearUserTimezone() {
            this.userTimezone = null
        },

        // Non-blocking system date/time bootstrap shared by admin.js and client.js.
        // UTC fallback stays active until this resolves (or forever, on failure).
        async bootstrap() {
            try {
                const res = await http.get('/settings/system-data')
                const s = res.data?.data?.settings ?? {}
                this.init({
                    timezone:   s.timezone?.name ?? 'UTC',
                    dateFormat: s.date_format    ?? 'd/m/Y',
                    timeFormat: s.time_format    ?? 'H:i',
                })
                // Re-apply user timezone after system data loads so it isn't overwritten
                const userTz = useAuthStore().user?.timezone?.name
                if (userTz) this.setUserTimezone(userTz)
            } catch {
                this.init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
            }
        },
    },
})
