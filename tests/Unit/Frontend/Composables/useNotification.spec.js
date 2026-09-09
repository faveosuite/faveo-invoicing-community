import { useNotification } from '@/core/composables/useNotification.js'

describe('useNotification', () => {
    beforeEach(() => {
        // Reset module-level state between tests
        const { dismiss } = useNotification()
        dismiss()
    })

    it('returns message, type, visible, notify, dismiss', () => {
        const result = useNotification()
        expect(result).toHaveProperty('message')
        expect(result).toHaveProperty('type')
        expect(result).toHaveProperty('visible')
        expect(result).toHaveProperty('notify')
        expect(result).toHaveProperty('dismiss')
    })

    it('notify sets message, type, and visible', () => {
        const { message, type, visible, notify } = useNotification()
        notify('Hello', 'success')
        expect(message.value).toBe('Hello')
        expect(type.value).toBe('success')
        expect(visible.value).toBe(true)
    })

    it('notify defaults type to success when not provided', () => {
        const { type, notify } = useNotification()
        notify('Test message')
        expect(type.value).toBe('success')
    })

    it('notify sets danger type correctly', () => {
        const { type, notify } = useNotification()
        notify('Error occurred', 'danger')
        expect(type.value).toBe('danger')
    })

    it('dismiss sets visible to false', () => {
        const { visible, notify, dismiss } = useNotification()
        notify('Hello', 'success')
        expect(visible.value).toBe(true)
        dismiss()
        expect(visible.value).toBe(false)
    })

    it('auto-dismisses after 7 seconds', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('Hello', 'success')
        expect(visible.value).toBe(true)
        jest.advanceTimersByTime(7000)
        expect(visible.value).toBe(false)
    })

    it('does not dismiss before 7 seconds', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('Hello', 'success')
        jest.advanceTimersByTime(6999)
        expect(visible.value).toBe(true)
    })

    it('second notify resets the auto-dismiss timer', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('First', 'success')
        jest.advanceTimersByTime(5000)
        // Second notify should reset the 7s timer
        notify('Second', 'info')
        jest.advanceTimersByTime(5000)
        // Timer from first notify would have fired by now (5+5=10s), but it was reset
        expect(visible.value).toBe(true)
        jest.advanceTimersByTime(2000)
        // Now 7s from second notify have elapsed
        expect(visible.value).toBe(false)
    })

    it('respects a custom duration argument', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('Hello', 'success', 40000)
        jest.advanceTimersByTime(7000)
        expect(visible.value).toBe(true)
        jest.advanceTimersByTime(33000)
        expect(visible.value).toBe(false)
    })

    it('shared state: multiple calls to useNotification() share the same refs', () => {
        const a = useNotification()
        const b = useNotification()
        a.notify('Shared', 'warning')
        expect(b.message.value).toBe('Shared')
        expect(b.visible.value).toBe(true)
    })
})
