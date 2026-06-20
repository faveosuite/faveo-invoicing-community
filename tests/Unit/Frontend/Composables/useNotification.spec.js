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

    it('auto-dismisses after 40 seconds', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('Hello', 'success')
        expect(visible.value).toBe(true)
        jest.advanceTimersByTime(40000)
        expect(visible.value).toBe(false)
    })

    it('does not dismiss before 40 seconds', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('Hello', 'success')
        jest.advanceTimersByTime(39999)
        expect(visible.value).toBe(true)
    })

    it('second notify resets the auto-dismiss timer', () => {
        jest.useFakeTimers()
        const { visible, notify } = useNotification()
        notify('First', 'success')
        jest.advanceTimersByTime(30000)
        // Second notify should reset the 40s timer
        notify('Second', 'info')
        jest.advanceTimersByTime(30000)
        // Timer from first notify would have fired by now (30+30=60s), but it was reset
        expect(visible.value).toBe(true)
        jest.advanceTimersByTime(10000)
        // Now 40s from second notify have elapsed
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
