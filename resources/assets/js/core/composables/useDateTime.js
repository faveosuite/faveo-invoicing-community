import { computed } from 'vue'
import { useDateTimeStore } from '../stores/dateTimeStore'
import { formatWithPattern, toUTC as _toUTC } from '../../helpers/luxonHelpers'

export function useDateTime() {
    const store = useDateTimeStore()

    const timezone        = computed(() => store.timezone)
    const dateFormat      = computed(() => store.luxonDateFormat)
    const timeFormat      = computed(() => store.luxonTimeFormat)
    const dateTimeFormat  = computed(() => store.luxonDateTimeFormat)

    function formatDate(utcString) {
        return formatWithPattern(utcString, timezone.value, dateFormat.value)
    }

    function formatTime(utcString) {
        return formatWithPattern(utcString, timezone.value, timeFormat.value)
    }

    function formatDateTime(utcString) {
        return formatWithPattern(utcString, timezone.value, dateTimeFormat.value)
    }

    function formatCustom(utcString, luxonFormat) {
        return formatWithPattern(utcString, timezone.value, luxonFormat)
    }

    function toUTC(localString, inputFormat = 'yyyy-MM-dd HH:mm:ss') {
        return _toUTC(localString, timezone.value, inputFormat)
    }

    return {
        formatDate,
        formatTime,
        formatDateTime,
        formatCustom,
        toUTC,
        timezone,
        dateFormat,
        timeFormat,
        dateTimeFormat,
        isReady: computed(() => store.isReady),
    }
}
