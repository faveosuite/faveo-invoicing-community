import { defineStore } from 'pinia'
import { phpToLuxon } from '../../helpers/luxonHelpers'

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
    },
})
