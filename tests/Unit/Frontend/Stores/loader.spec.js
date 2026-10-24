import { setActivePinia, createPinia } from 'pinia'
import { useLoaderStore } from '@/core/stores/loader.js'

describe('useLoaderStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('initial state', () => {
        it('has an empty axiosCallsStack', () => {
            const store = useLoaderStore()
            expect(store.axiosCallsStack).toEqual([])
        })
    })

    describe('showLoader getter', () => {
        it('returns false when stack is empty', () => {
            const store = useLoaderStore()
            expect(store.showLoader).toBe(false)
        })

        it('returns true after startLoader is called', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            expect(store.showLoader).toBe(true)
        })

        it('returns false when all loaders are stopped', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.stopLoader('req-1')
            expect(store.showLoader).toBe(false)
        })
    })

    describe('startLoader', () => {
        it('adds a loader instance to the stack', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            expect(store.axiosCallsStack).toContain('req-1')
        })

        it('can track multiple distinct instances', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.startLoader('req-2')
            expect(store.axiosCallsStack).toHaveLength(2)
        })

        it('does not add the same instance twice', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.startLoader('req-1')
            expect(store.axiosCallsStack).toHaveLength(1)
        })

        it('throws when called without an argument', () => {
            const store = useLoaderStore()
            expect(() => store.startLoader()).toThrow('Loader instance is required!')
        })

        it('throws when called with null', () => {
            const store = useLoaderStore()
            expect(() => store.startLoader(null)).toThrow('Loader instance is required!')
        })
    })

    describe('stopLoader', () => {
        it('removes the loader instance from the stack', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.stopLoader('req-1')
            expect(store.axiosCallsStack).not.toContain('req-1')
        })

        it('only removes the matching instance', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.startLoader('req-2')
            store.stopLoader('req-1')
            expect(store.axiosCallsStack).toContain('req-2')
            expect(store.axiosCallsStack).not.toContain('req-1')
        })

        it('is a no-op when instance is not in the stack', () => {
            const store = useLoaderStore()
            store.stopLoader('not-started')
            expect(store.axiosCallsStack).toHaveLength(0)
        })

        it('throws when called without an argument', () => {
            const store = useLoaderStore()
            expect(() => store.stopLoader()).toThrow('Loader instance is required!')
        })

        it('throws when called with null', () => {
            const store = useLoaderStore()
            expect(() => store.stopLoader(null)).toThrow('Loader instance is required!')
        })
    })

    describe('forceStopLoader', () => {
        it('clears all entries from the stack', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.startLoader('req-2')
            store.startLoader('req-3')
            store.forceStopLoader()
            expect(store.axiosCallsStack).toHaveLength(0)
        })

        it('sets showLoader to false', () => {
            const store = useLoaderStore()
            store.startLoader('req-1')
            store.forceStopLoader()
            expect(store.showLoader).toBe(false)
        })

        it('is safe to call on an already-empty stack', () => {
            const store = useLoaderStore()
            store.forceStopLoader()
            expect(store.axiosCallsStack).toHaveLength(0)
        })
    })
})
