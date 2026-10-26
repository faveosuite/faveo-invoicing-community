jest.mock('@/helpers/formUtils.js', () => ({
    scrollToFirstError: jest.fn(),
}))

import { useScrollToError } from '@/core/composables/useScrollToError.js'
import { scrollToFirstError } from '@/helpers/formUtils.js'

describe('useScrollToError', () => {
    it('returns a scrollToFirstError function', () => {
        const result = useScrollToError()
        expect(result).toHaveProperty('scrollToFirstError')
        expect(typeof result.scrollToFirstError).toBe('function')
    })

    it('returned scrollToFirstError is the same function from formUtils', () => {
        const { scrollToFirstError: fn } = useScrollToError()
        expect(fn).toBe(scrollToFirstError)
    })

    it('calling scrollToFirstError delegates to formUtils', () => {
        const { scrollToFirstError: fn } = useScrollToError()
        fn()
        expect(scrollToFirstError).toHaveBeenCalledTimes(1)
    })

    it('forwards arguments to the underlying function', () => {
        const { scrollToFirstError: fn } = useScrollToError()
        fn('someArg')
        expect(scrollToFirstError).toHaveBeenCalledWith('someArg')
    })
})
