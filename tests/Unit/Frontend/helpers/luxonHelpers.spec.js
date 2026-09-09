import { phpToLuxon, utcToZone, formatWithPattern, toUTC } from '@/helpers/luxonHelpers.js'
import { DateTime } from 'luxon'

// ── phpToLuxon ────────────────────────────────────────────────────────────────
describe('phpToLuxon', () => {
    it('returns default format when called with empty string', () => {
        expect(phpToLuxon('')).toBe('yyyy-MM-dd HH:mm')
    })

    it('returns default format when called with null', () => {
        expect(phpToLuxon(null)).toBe('yyyy-MM-dd HH:mm')
    })

    it('returns default format when called with undefined', () => {
        expect(phpToLuxon(undefined)).toBe('yyyy-MM-dd HH:mm')
    })

    it('converts d → dd (zero-padded day)', () => {
        expect(phpToLuxon('d')).toBe('dd')
    })

    it('converts j → d (day without padding)', () => {
        expect(phpToLuxon('j')).toBe('d')
    })

    it('converts m → MM (zero-padded month)', () => {
        expect(phpToLuxon('m')).toBe('MM')
    })

    it('converts n → M (month without padding)', () => {
        expect(phpToLuxon('n')).toBe('M')
    })

    it('converts Y → yyyy (4-digit year)', () => {
        expect(phpToLuxon('Y')).toBe('yyyy')
    })

    it('converts y → yy (2-digit year)', () => {
        expect(phpToLuxon('y')).toBe('yy')
    })

    it('converts F → MMMM (full month name)', () => {
        expect(phpToLuxon('F')).toBe('MMMM')
    })

    it('converts M → MMM (short month name)', () => {
        expect(phpToLuxon('M')).toBe('MMM')
    })

    it('converts H → HH (24h hour, zero-padded)', () => {
        expect(phpToLuxon('H')).toBe('HH')
    })

    it('converts h → hh (12h hour, zero-padded)', () => {
        expect(phpToLuxon('h')).toBe('hh')
    })

    it('converts g → h (12h hour without padding)', () => {
        expect(phpToLuxon('g')).toBe('h')
    })

    it('converts i → mm (minutes)', () => {
        expect(phpToLuxon('i')).toBe('mm')
    })

    it('converts s → ss (seconds)', () => {
        expect(phpToLuxon('s')).toBe('ss')
    })

    it('converts A → a (AM/PM uppercase token)', () => {
        expect(phpToLuxon('A')).toBe('a')
    })

    it('converts a → a (am/pm lowercase token)', () => {
        expect(phpToLuxon('a')).toBe('a')
    })

    it('converts a composite date format d/m/Y', () => {
        expect(phpToLuxon('d/m/Y')).toBe('dd/MM/yyyy')
    })

    it('converts a composite datetime format d/m/Y H:i', () => {
        expect(phpToLuxon('d/m/Y H:i')).toBe('dd/MM/yyyy HH:mm')
    })

    it('converts H:i (time only)', () => {
        expect(phpToLuxon('H:i')).toBe('HH:mm')
    })

    it('converts Y-m-d', () => {
        expect(phpToLuxon('Y-m-d')).toBe('yyyy-MM-dd')
    })

    it('passes through non-PHP characters unchanged (separators)', () => {
        expect(phpToLuxon('d-m-Y')).toBe('dd-MM-yyyy')
    })
})

// ── utcToZone ─────────────────────────────────────────────────────────────────
describe('utcToZone', () => {
    it('returns null for null input', () => {
        expect(utcToZone(null)).toBeNull()
    })

    it('returns null for undefined input', () => {
        expect(utcToZone(undefined)).toBeNull()
    })

    it('returns null for empty string', () => {
        expect(utcToZone('')).toBeNull()
    })

    it('returns null for a completely invalid string', () => {
        expect(utcToZone('not-a-date')).toBeNull()
    })

    it('parses a valid ISO 8601 UTC string', () => {
        const dt = utcToZone('2024-03-15T10:30:00Z', 'UTC')
        expect(dt).not.toBeNull()
        expect(dt.isValid).toBe(true)
        expect(dt.hour).toBe(10)
        expect(dt.minute).toBe(30)
    })

    it('parses a valid SQL datetime string (Laravel/MySQL format)', () => {
        const dt = utcToZone('2024-03-15 10:30:00', 'UTC')
        expect(dt).not.toBeNull()
        expect(dt.isValid).toBe(true)
        expect(dt.hour).toBe(10)
    })

    it('converts UTC to Asia/Kolkata (UTC+5:30)', () => {
        const dt = utcToZone('2024-01-01T00:00:00Z', 'Asia/Kolkata')
        expect(dt).not.toBeNull()
        expect(dt.isValid).toBe(true)
        expect(dt.hour).toBe(5)
        expect(dt.minute).toBe(30)
    })

    it('defaults to UTC timezone when no timezone provided', () => {
        const dt = utcToZone('2024-06-01T12:00:00Z')
        expect(dt).not.toBeNull()
        expect(dt.zoneName).toBe('UTC')
    })

    it('returns a Luxon DateTime instance', () => {
        const dt = utcToZone('2024-06-01T12:00:00Z', 'UTC')
        expect(dt).toBeInstanceOf(DateTime)
    })
})

// ── formatWithPattern ─────────────────────────────────────────────────────────
describe('formatWithPattern', () => {
    it('formats a valid UTC string using the provided Luxon format', () => {
        const result = formatWithPattern('2024-03-15T10:30:00Z', 'UTC', 'yyyy-MM-dd')
        expect(result).toBe('2024-03-15')
    })

    it('returns the em-dash fallback for null input', () => {
        expect(formatWithPattern(null, 'UTC', 'yyyy-MM-dd')).toBe('—')
    })

    it('returns the em-dash fallback for undefined input', () => {
        expect(formatWithPattern(undefined, 'UTC', 'yyyy-MM-dd')).toBe('—')
    })

    it('returns the em-dash fallback for empty string input', () => {
        expect(formatWithPattern('', 'UTC', 'yyyy-MM-dd')).toBe('—')
    })

    it('returns the em-dash fallback for an invalid date string', () => {
        expect(formatWithPattern('not-a-date', 'UTC', 'yyyy-MM-dd')).toBe('—')
    })

    it('formats with time component', () => {
        const result = formatWithPattern('2024-03-15T14:05:00Z', 'UTC', 'HH:mm')
        expect(result).toBe('14:05')
    })

    it('applies timezone conversion before formatting', () => {
        // UTC midnight → Asia/Kolkata 05:30
        const result = formatWithPattern('2024-01-01T00:00:00Z', 'Asia/Kolkata', 'HH:mm')
        expect(result).toBe('05:30')
    })

    it('formats a SQL datetime string', () => {
        const result = formatWithPattern('2024-06-20 09:00:00', 'UTC', 'dd/MM/yyyy')
        expect(result).toBe('20/06/2024')
    })
})

// ── toUTC ─────────────────────────────────────────────────────────────────────
describe('toUTC', () => {
    it('returns empty string for null input', () => {
        expect(toUTC(null, 'UTC')).toBe('')
    })

    it('returns empty string for undefined input', () => {
        expect(toUTC(undefined, 'UTC')).toBe('')
    })

    it('returns empty string for empty string input', () => {
        expect(toUTC('', 'UTC')).toBe('')
    })

    it('converts a local time in Asia/Kolkata to UTC', () => {
        const result = toUTC('2026-06-12 10:00:00', 'Asia/Kolkata')
        // 10:00 IST (UTC+5:30) = 04:30 UTC
        expect(result).toBe('2026-06-12 04:30:00')
    })

    it('returns the string unchanged for UTC timezone (no offset)', () => {
        const result = toUTC('2026-06-12 10:00:00', 'UTC')
        expect(result).toBe('2026-06-12 10:00:00')
    })

    it('supports a custom input format', () => {
        const result = toUTC('12/06/2026 10:00', 'UTC', 'dd/MM/yyyy HH:mm')
        expect(result).toBe('2026-06-12 10:00:00')
    })

    it('returns the original string when the input does not match the format', () => {
        const result = toUTC('not-a-date', 'UTC')
        expect(result).toBe('not-a-date')
    })
})
