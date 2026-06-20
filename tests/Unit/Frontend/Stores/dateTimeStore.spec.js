import { setActivePinia, createPinia } from 'pinia'
import { useDateTimeStore } from '@/core/stores/dateTimeStore.js'

describe('useDateTimeStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('initial state', () => {
        it('has systemTimezone of UTC', () => {
            const store = useDateTimeStore()
            expect(store.systemTimezone).toBe('UTC')
        })

        it('has userTimezone of null', () => {
            const store = useDateTimeStore()
            expect(store.userTimezone).toBeNull()
        })

        it('has dateFormat of d/m/Y', () => {
            const store = useDateTimeStore()
            expect(store.dateFormat).toBe('d/m/Y')
        })

        it('has timeFormat of H:i', () => {
            const store = useDateTimeStore()
            expect(store.timeFormat).toBe('H:i')
        })

        it('has isReady of false', () => {
            const store = useDateTimeStore()
            expect(store.isReady).toBe(false)
        })
    })

    describe('timezone getter', () => {
        it('returns systemTimezone when userTimezone is null', () => {
            const store = useDateTimeStore()
            expect(store.timezone).toBe('UTC')
        })

        it('returns userTimezone when set', () => {
            const store = useDateTimeStore()
            store.setUserTimezone('America/New_York')
            expect(store.timezone).toBe('America/New_York')
        })

        it('falls back to systemTimezone after clearUserTimezone', () => {
            const store = useDateTimeStore()
            store.setUserTimezone('Asia/Tokyo')
            store.clearUserTimezone()
            expect(store.timezone).toBe('UTC')
        })
    })

    describe('luxonDateFormat getter', () => {
        it('converts d/m/Y to dd/MM/yyyy', () => {
            const store = useDateTimeStore()
            expect(store.luxonDateFormat).toBe('dd/MM/yyyy')
        })

        it('updates after init changes dateFormat', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'UTC', dateFormat: 'Y-m-d', timeFormat: 'H:i' })
            expect(store.luxonDateFormat).toBe('yyyy-MM-dd')
        })
    })

    describe('luxonTimeFormat getter', () => {
        it('converts H:i to HH:mm', () => {
            const store = useDateTimeStore()
            expect(store.luxonTimeFormat).toBe('HH:mm')
        })

        it('updates after init changes timeFormat', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: 'h:i A' })
            expect(store.luxonTimeFormat).toBe('hh:mm a')
        })
    })

    describe('luxonDateTimeFormat getter', () => {
        it('combines date and time formats with a space', () => {
            const store = useDateTimeStore()
            expect(store.luxonDateTimeFormat).toBe('dd/MM/yyyy HH:mm')
        })

        it('updates after init', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'UTC', dateFormat: 'Y-m-d', timeFormat: 'H:i:s' })
            expect(store.luxonDateTimeFormat).toBe('yyyy-MM-dd HH:mm:ss')
        })
    })

    describe('init', () => {
        it('sets systemTimezone, dateFormat, timeFormat and isReady', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'Asia/Kolkata', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
            expect(store.systemTimezone).toBe('Asia/Kolkata')
            expect(store.dateFormat).toBe('d/m/Y')
            expect(store.timeFormat).toBe('H:i')
            expect(store.isReady).toBe(true)
        })

        it('falls back to UTC when timezone is falsy', () => {
            const store = useDateTimeStore()
            store.init({ timezone: '', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
            expect(store.systemTimezone).toBe('UTC')
        })

        it('falls back to d/m/Y when dateFormat is falsy', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'UTC', dateFormat: '', timeFormat: 'H:i' })
            expect(store.dateFormat).toBe('d/m/Y')
        })

        it('falls back to H:i when timeFormat is falsy', () => {
            const store = useDateTimeStore()
            store.init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: '' })
            expect(store.timeFormat).toBe('H:i')
        })

        it('sets isReady to true', () => {
            const store = useDateTimeStore()
            expect(store.isReady).toBe(false)
            store.init({ timezone: 'UTC', dateFormat: 'd/m/Y', timeFormat: 'H:i' })
            expect(store.isReady).toBe(true)
        })
    })

    describe('setUserTimezone', () => {
        it('sets userTimezone to the provided value', () => {
            const store = useDateTimeStore()
            store.setUserTimezone('Europe/London')
            expect(store.userTimezone).toBe('Europe/London')
        })

        it('sets userTimezone to null when called with a falsy value', () => {
            const store = useDateTimeStore()
            store.setUserTimezone('Europe/London')
            store.setUserTimezone('')
            expect(store.userTimezone).toBeNull()
        })

        it('sets userTimezone to null when called with null', () => {
            const store = useDateTimeStore()
            store.setUserTimezone(null)
            expect(store.userTimezone).toBeNull()
        })
    })

    describe('clearUserTimezone', () => {
        it('resets userTimezone to null', () => {
            const store = useDateTimeStore()
            store.setUserTimezone('America/Chicago')
            store.clearUserTimezone()
            expect(store.userTimezone).toBeNull()
        })

        it('is safe to call when userTimezone is already null', () => {
            const store = useDateTimeStore()
            store.clearUserTimezone()
            expect(store.userTimezone).toBeNull()
        })
    })
})
