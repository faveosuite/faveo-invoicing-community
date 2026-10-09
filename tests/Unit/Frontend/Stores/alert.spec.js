import { setActivePinia, createPinia } from 'pinia'
import { useAlertStore } from '@/core/stores/alert.js'

describe('useAlertStore', () => {
    beforeEach(() => {
        setActivePinia(createPinia())
    })

    describe('initial state', () => {
        it('has empty message', () => {
            const store = useAlertStore()
            expect(store.message).toBe('')
        })

        it('has empty type', () => {
            const store = useAlertStore()
            expect(store.type).toBe('')
        })

        it('has empty component_name', () => {
            const store = useAlertStore()
            expect(store.component_name).toBe('')
        })

        it('has empty duration', () => {
            const store = useAlertStore()
            expect(store.duration).toBe('')
        })
    })

    describe('setAlert', () => {
        it('sets message, type and component_name', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Saved!', type: 'success', component_name: 'cart' })
            expect(store.message).toBe('Saved!')
            expect(store.type).toBe('success')
            expect(store.component_name).toBe('cart')
        })

        it('sets duration when provided', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Hi', type: 'info', component_name: 'nav', duration: 3000 })
            expect(store.duration).toBe(3000)
        })

        it('defaults duration to empty string when not provided', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Hi', type: 'info', component_name: 'nav' })
            expect(store.duration).toBe('')
        })

        it('overwrites a previous alert', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'First', type: 'error', component_name: 'a' })
            store.setAlert({ message: 'Second', type: 'success', component_name: 'b' })
            expect(store.message).toBe('Second')
            expect(store.type).toBe('success')
            expect(store.component_name).toBe('b')
        })

        it('handles error type', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Something went wrong', type: 'error', component_name: 'form' })
            expect(store.type).toBe('error')
        })

        it('handles warning type', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Watch out', type: 'warning', component_name: 'page' })
            expect(store.type).toBe('warning')
        })
    })

    describe('unsetAlert', () => {
        it('clears all fields after setAlert', () => {
            const store = useAlertStore()
            store.setAlert({ message: 'Done', type: 'success', component_name: 'cart', duration: 2000 })
            store.unsetAlert()
            expect(store.message).toBe('')
            expect(store.type).toBe('')
            expect(store.component_name).toBe('')
            expect(store.duration).toBe('')
        })

        it('is idempotent when called on an already-empty store', () => {
            const store = useAlertStore()
            store.unsetAlert()
            expect(store.message).toBe('')
            expect(store.type).toBe('')
            expect(store.component_name).toBe('')
            expect(store.duration).toBe('')
        })
    })
})
