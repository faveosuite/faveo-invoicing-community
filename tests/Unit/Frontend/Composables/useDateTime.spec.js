jest.mock('@/helpers/luxonHelpers', () => ({
    formatWithPattern: jest.fn((v) => v),
    toUTC: jest.fn((v) => v),
    phpToLuxon: jest.fn((v) => v),
}))

import { useDateTime } from '@/core/composables/useDateTime.js'
import { formatWithPattern, toUTC as mockToUTC } from '@/helpers/luxonHelpers'
import { useDateTimeStore } from '@/core/stores/dateTimeStore'

describe('useDateTime', () => {
    let store

    beforeEach(() => {
        store = useDateTimeStore()
        store.init({
            timezone:   'America/New_York',
            dateFormat: 'MM/dd/yyyy',
            timeFormat: 'HH:mm',
        })
        formatWithPattern.mockClear()
        mockToUTC.mockClear()
    })

    it('returns all expected exports', () => {
        const result = useDateTime()
        expect(result).toHaveProperty('formatDate')
        expect(result).toHaveProperty('formatTime')
        expect(result).toHaveProperty('formatDateTime')
        expect(result).toHaveProperty('formatCustom')
        expect(result).toHaveProperty('toUTC')
        expect(result).toHaveProperty('timezone')
        expect(result).toHaveProperty('dateFormat')
        expect(result).toHaveProperty('timeFormat')
        expect(result).toHaveProperty('dateTimeFormat')
        expect(result).toHaveProperty('isReady')
    })

    it('isReady reflects store.isReady', () => {
        const { isReady } = useDateTime()
        expect(isReady.value).toBe(true)
    })

    it('isReady is false before store.init()', () => {
        // Create fresh pinia store with default (not-ready) state
        store.$reset()
        const { isReady } = useDateTime()
        expect(isReady.value).toBe(false)
    })

    it('timezone computed returns userTimezone when set', () => {
        store.setUserTimezone('Europe/London')
        const { timezone } = useDateTime()
        expect(timezone.value).toBe('Europe/London')
    })

    it('timezone computed falls back to systemTimezone when userTimezone is null', () => {
        store.clearUserTimezone()
        const { timezone } = useDateTime()
        expect(timezone.value).toBe('America/New_York')
    })

    it('formatDate delegates to formatWithPattern with correct args', () => {
        const { formatDate, timezone, dateFormat } = useDateTime()
        formatDate('2024-01-15T00:00:00Z')
        expect(formatWithPattern).toHaveBeenCalledWith(
            '2024-01-15T00:00:00Z',
            timezone.value,
            dateFormat.value,
        )
    })

    it('formatTime delegates to formatWithPattern with correct args', () => {
        const { formatTime, timezone, timeFormat } = useDateTime()
        formatTime('2024-01-15T12:30:00Z')
        expect(formatWithPattern).toHaveBeenCalledWith(
            '2024-01-15T12:30:00Z',
            timezone.value,
            timeFormat.value,
        )
    })

    it('formatDateTime delegates to formatWithPattern with correct args', () => {
        const { formatDateTime, timezone, dateTimeFormat } = useDateTime()
        formatDateTime('2024-01-15T12:30:00Z')
        expect(formatWithPattern).toHaveBeenCalledWith(
            '2024-01-15T12:30:00Z',
            timezone.value,
            dateTimeFormat.value,
        )
    })

    it('formatCustom delegates to formatWithPattern with provided luxon format', () => {
        const { formatCustom, timezone } = useDateTime()
        formatCustom('2024-01-15T00:00:00Z', 'dd LLL yyyy')
        expect(formatWithPattern).toHaveBeenCalledWith(
            '2024-01-15T00:00:00Z',
            timezone.value,
            'dd LLL yyyy',
        )
    })

    it('toUTC delegates to luxonHelpers toUTC with timezone and format', () => {
        const { toUTC, timezone } = useDateTime()
        toUTC('2024-01-15 12:00:00')
        expect(mockToUTC).toHaveBeenCalledWith(
            '2024-01-15 12:00:00',
            timezone.value,
            'yyyy-MM-dd HH:mm:ss',
        )
    })

    it('toUTC accepts custom inputFormat', () => {
        const { toUTC, timezone } = useDateTime()
        toUTC('15/01/2024', 'dd/MM/yyyy')
        expect(mockToUTC).toHaveBeenCalledWith(
            '15/01/2024',
            timezone.value,
            'dd/MM/yyyy',
        )
    })
})
