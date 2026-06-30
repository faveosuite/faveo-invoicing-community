import {
    getIdFromUrl,
    findObjectByKey,
    lang,
    flatten,
    boolean,
    getSubStringValue,
    generateRandomString,
    getCountry,
} from '@/helpers/extraLogics.js'

// formatDateTime depends on useDateTime composable (Pinia + store) — tested
// separately via composable tests. We just verify it re-exports a callable.
import * as extraLogics from '@/helpers/extraLogics.js'

// ── getIdFromUrl ──────────────────────────────────────────────────────────────
describe('getIdFromUrl', () => {
    it('extracts numeric id from a standard edit URL', () => {
        expect(getIdFromUrl('/products/42/edit')).toBe('42')
    })

    it('extracts numeric id from a simple resource URL', () => {
        expect(getIdFromUrl('/users/7')).toBe('7')
    })

    it('returns the last numeric segment when multiple numbers appear', () => {
        expect(getIdFromUrl('/category/3/product/99')).toBe('99')
    })

    it('returns undefined for a URL with no numeric segment', () => {
        expect(getIdFromUrl('/create')).toBeUndefined()
    })

    it('returns undefined for the root path', () => {
        expect(getIdFromUrl('/')).toBeUndefined()
    })

    it('handles a bare numeric string', () => {
        expect(getIdFromUrl('123')).toBe('123')
    })

    it('handles an empty string (no segments)', () => {
        expect(getIdFromUrl('')).toBeUndefined()
    })
})

// ── findObjectByKey ───────────────────────────────────────────────────────────
describe('findObjectByKey', () => {
    const arr = [
        { id: 1, name: 'alpha' },
        { id: 2, name: 'beta' },
        { id: 3, name: 'gamma' },
    ]

    it('finds an object by a matching numeric key/value', () => {
        expect(findObjectByKey(arr, 'id', 2)).toEqual({ id: 2, name: 'beta' })
    })

    it('finds an object by a matching string key/value', () => {
        expect(findObjectByKey(arr, 'name', 'gamma')).toEqual({ id: 3, name: 'gamma' })
    })

    it('returns null when the value does not exist', () => {
        expect(findObjectByKey(arr, 'id', 99)).toBeNull()
    })

    it('returns null for an empty array', () => {
        expect(findObjectByKey([], 'id', 1)).toBeNull()
    })

    it('returns null when searching for null value and no match', () => {
        expect(findObjectByKey(arr, 'id', null)).toBeNull()
    })

    it('returns null when searching for undefined value', () => {
        expect(findObjectByKey(arr, 'id', undefined)).toBeNull()
    })

    it('uses loose equality — finds "1" when stored value is 1', () => {
        expect(findObjectByKey(arr, 'id', '1')).toEqual({ id: 1, name: 'alpha' })
    })

    it('returns the first match when duplicates exist', () => {
        const dups = [{ id: 1, v: 'first' }, { id: 1, v: 'second' }]
        expect(findObjectByKey(dups, 'id', 1)).toEqual({ id: 1, v: 'first' })
    })
})

// ── lang ─────────────────────────────────────────────────────────────────────
describe('lang', () => {
    afterEach(() => {
        delete globalThis.translator
    })

    it('returns the original string when translator is not defined', () => {
        expect(lang('Hello')).toBe('Hello')
    })

    it('returns the original string when translator.lang is falsy', () => {
        globalThis.translator = { lang: null }
        expect(lang('Hello')).toBe('Hello')
    })

    it('returns translated string when translator.lang has the key', () => {
        globalThis.translator = { lang: { Hello: 'Hola' } }
        expect(lang('Hello')).toBe('Hola')
    })

    it('falls back to original string when key is missing from translator.lang', () => {
        globalThis.translator = { lang: { Goodbye: 'Adios' } }
        expect(lang('Hello')).toBe('Hello')
    })

    it('returns empty string for empty string key', () => {
        expect(lang('')).toBe('')
    })

    it('returns the key unchanged for null-like input when no translator', () => {
        expect(lang(undefined)).toBeUndefined()
    })
})

// ── flatten ───────────────────────────────────────────────────────────────────
describe('flatten', () => {
    it('flattens an array of arrays by one level', () => {
        expect(flatten([[1, 2], [3, 4]])).toEqual([1, 2, 3, 4])
    })

    it('flattens an object of arrays', () => {
        const input = { a: [{ id: 1 }, { id: 2 }], b: [{ id: 3 }] }
        expect(flatten(input)).toEqual([{ id: 1 }, { id: 2 }, { id: 3 }])
    })

    it('flattens a single-key object', () => {
        expect(flatten({ x: [10, 20, 30] })).toEqual([10, 20, 30])
    })

    it('returns empty array for empty array input', () => {
        expect(flatten([])).toEqual([])
    })

    it('returns empty array for empty object input', () => {
        expect(flatten({})).toEqual([])
    })

    it('handles array of primitives (no nesting)', () => {
        expect(flatten([1, 2, 3])).toEqual([1, 2, 3])
    })
})

// ── boolean ───────────────────────────────────────────────────────────────────
describe('boolean', () => {
    it('returns false for numeric 0', () => {
        expect(boolean(0)).toBe(false)
    })

    it('returns false for string "0"', () => {
        expect(boolean('0')).toBe(false)
    })

    it('returns false for null', () => {
        expect(boolean(null)).toBe(false)
    })

    it('returns false for empty string', () => {
        expect(boolean('')).toBe(false)
    })

    it('returns false for undefined', () => {
        expect(boolean(undefined)).toBe(false)
    })

    it('returns false for boolean false', () => {
        expect(boolean(false)).toBe(false)
    })

    it('returns false for empty array', () => {
        expect(boolean([])).toBe(false)
    })

    it('returns true for numeric 1', () => {
        expect(boolean(1)).toBe(true)
    })

    it('returns true for string "1"', () => {
        expect(boolean('1')).toBe(true)
    })

    it('returns true for string "true"', () => {
        expect(boolean('true')).toBe(true)
    })

    it('returns true for string "false" (non-empty string is truthy)', () => {
        expect(boolean('false')).toBe(true)
    })

    it('returns true for boolean true', () => {
        expect(boolean(true)).toBe(true)
    })

    it('returns true for a non-empty array', () => {
        expect(boolean([1])).toBe(true)
    })

    it('returns true for a positive number', () => {
        expect(boolean(42)).toBe(true)
    })

    it('returns true for a non-empty object', () => {
        expect(boolean({ a: 1 })).toBe(true)
    })
})

// ── getSubStringValue ─────────────────────────────────────────────────────────
describe('getSubStringValue', () => {
    it('truncates name longer than count and appends ellipsis', () => {
        expect(getSubStringValue('Hello World', 5)).toBe('Hello...')
    })

    it('returns full name when length equals count', () => {
        expect(getSubStringValue('Hello', 5)).toBe('Hello')
    })

    it('returns full name when length is less than count', () => {
        expect(getSubStringValue('Hi', 10)).toBe('Hi')
    })

    it('returns undefined for empty string (falsy guard)', () => {
        expect(getSubStringValue('', 5)).toBeUndefined()
    })

    it('returns undefined for null input', () => {
        expect(getSubStringValue(null, 5)).toBeUndefined()
    })

    it('returns undefined for undefined input', () => {
        expect(getSubStringValue(undefined, 5)).toBeUndefined()
    })

    it('truncates correctly at count = 0', () => {
        expect(getSubStringValue('test', 0)).toBe('...')
    })

    it('handles a single character name with count 1', () => {
        expect(getSubStringValue('A', 1)).toBe('A')
    })
})

// ── generateRandomString ──────────────────────────────────────────────────────
describe('generateRandomString', () => {
    it('generates a string of the requested length', () => {
        expect(generateRandomString(8)).toHaveLength(8)
    })

    it('uses default length of 16 when no argument provided', () => {
        expect(generateRandomString()).toHaveLength(16)
    })

    it('generates a string of length 1', () => {
        expect(generateRandomString(1)).toHaveLength(1)
    })

    it('generates a string of length 32', () => {
        expect(generateRandomString(32)).toHaveLength(32)
    })

    it('only contains alphanumeric uppercase characters', () => {
        const result = generateRandomString(100)
        expect(result).toMatch(/^[A-Z0-9]+$/)
    })

    it('produces different strings on subsequent calls', () => {
        const a = generateRandomString(16)
        const b = generateRandomString(16)
        // Extremely unlikely to be equal; this guards against a static return
        expect(a).not.toBe(b)
    })
})

// ── formatDateTime (re-export smoke test) ────────────────────────────────────
describe('formatDateTime', () => {
    it('is exported as a function', () => {
        expect(typeof extraLogics.formatDateTime).toBe('function')
    })
})

// ── getCountry ────────────────────────────────────────────────────────────────
describe('getCountry', () => {
    beforeEach(() => {
        globalThis.fetch = jest.fn()
    })

    afterEach(() => {
        delete globalThis.fetch
    })

    it('returns a 2-letter country code on success', async () => {
        globalThis.fetch.mockResolvedValue({
            text: () => Promise.resolve('1;US;USA'),
        })
        const result = await getCountry()
        expect(result).toBe('US')
    })

    it('throws when the API returns a non-1 prefix', async () => {
        globalThis.fetch.mockResolvedValue({
            text: () => Promise.resolve('0;'),
        })
        await expect(getCountry()).rejects.toThrow('unable to fetch the country')
    })

    it('throws when the API returns an empty string', async () => {
        globalThis.fetch.mockResolvedValue({
            text: () => Promise.resolve(''),
        })
        await expect(getCountry()).rejects.toThrow('unable to fetch the country')
    })
})
