import { DateTime } from 'luxon'

const PHP_TO_LUXON = {
    'd': 'dd',   'j': 'd',
    'm': 'MM',   'n': 'M',
    'Y': 'yyyy', 'y': 'yy',
    'F': 'MMMM', 'M': 'MMM',
    'H': 'HH',   'h': 'hh',  'g': 'h',
    'i': 'mm',   's': 'ss',
    'A': 'a',    'a': 'a',
}

export function phpToLuxon(phpFormat) {
    if (!phpFormat) return 'yyyy-MM-dd HH:mm'
    return phpFormat.replace(/[a-zA-Z]/g, char => PHP_TO_LUXON[char] ?? char)
}

export function utcToZone(utcString, timezone = 'UTC') {
    if (!utcString) return null

    // Try ISO format first (e.g. 2018-12-13T00:00:00Z)
    let dt = DateTime.fromISO(utcString, { zone: 'utc' })

    // Fall back to SQL format (e.g. 2018-12-13 00:00:00) — what Laravel/MySQL returns
    if (!dt.isValid) {
        dt = DateTime.fromSQL(utcString, { zone: 'utc' })
    }

    if (!dt.isValid) return null
    return dt.setZone(timezone)
}

export function formatWithPattern(utcString, timezone, luxonFormat) {
    const dt = utcToZone(utcString, timezone)
    if (!dt) return '—'
    return dt.toFormat(luxonFormat)
}

/**
 * Convert a local datetime string (in user's timezone) to UTC before saving to backend.
 * Only use for datetime fields that include a time component — NOT for date-only fields.
 *
 * @param {string} localString  - e.g. '2026-06-12 10:00:00'
 * @param {string} timezone     - IANA timezone e.g. 'Asia/Kolkata'
 * @param {string} inputFormat  - Luxon format of the input string (default: 'yyyy-MM-dd HH:mm:ss')
 * @returns {string}            - UTC datetime string e.g. '2026-06-12 04:30:00'
 */
export function toUTC(localString, timezone, inputFormat = 'yyyy-MM-dd HH:mm:ss') {
    if (!localString) return ''
    const dt = DateTime.fromFormat(localString, inputFormat, { zone: timezone })
    if (!dt.isValid) return localString
    return dt.toUTC().toFormat('yyyy-MM-dd HH:mm:ss')
}
