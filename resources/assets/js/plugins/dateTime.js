import { useDateTimeStore } from '../core/stores/dateTimeStore'
import { formatWithPattern, phpToLuxon } from '../helpers/luxonHelpers'

const DateTimePlugin = {
    install(app) {
        const $dt = {
            format(utcString) {
                const store = useDateTimeStore()
                return formatWithPattern(utcString, store.timezone, store.luxonDateTimeFormat)
            },
            formatDate(utcString) {
                const store = useDateTimeStore()
                return formatWithPattern(utcString, store.timezone, store.luxonDateFormat)
            },
            formatTime(utcString) {
                const store = useDateTimeStore()
                return formatWithPattern(utcString, store.timezone, store.luxonTimeFormat)
            },
            formatCustom(utcString, luxonFormat) {
                const store = useDateTimeStore()
                return formatWithPattern(utcString, store.timezone, luxonFormat)
            },
            phpToLuxon,
        }

        app.config.globalProperties.$dt = $dt
        app.provide('$dt', $dt)
    },
}

export default DateTimePlugin
