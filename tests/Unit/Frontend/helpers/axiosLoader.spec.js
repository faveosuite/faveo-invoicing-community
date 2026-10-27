jest.mock('@/helpers/extraLogics', () => ({ lang: (key) => key, getIdFromUrl: jest.fn(() => 0) }))

/**
 * setupLoaderInterceptors(): the top progress bar driven by request/response counts.
 * Each scenario replays a fixed request/response sequence and records every progress call.
 * The module keeps its counters at module level, so every scenario loads a fresh copy.
 */
function run(steps) {
    let http
    let setupLoaderInterceptors
    jest.isolateModules(() => {
        ({ default: http, setupLoaderInterceptors } = require('@/plugins/axios'))
    })

    const calls = []
    let value = 0
    const progress = {
        start:    () => { calls.push('start'); value = 10 },
        finish:   () => { calls.push('finish'); value = 0 },
        set:      n => calls.push(`set ${n}`),
        increase: n => calls.push(`increase ${n}`),
        get:      () => value,
    }
    setupLoaderInterceptors(progress)

    // The loader's handlers are the last ones registered.
    const request  = http.interceptors.request.handlers.at(-1).fulfilled
    const response = http.interceptors.response.handlers.at(-1)

    for (const step of steps) {
        if (step === 'req') request({})
        else if (step === 'err') response.rejected(new Error('x')).catch(() => {})
        else if (step === 'idle') value = 0
        else if (step === 'wait90s') jest.advanceTimersByTime(90000)
        else response.fulfilled({ status: step })
        calls.push('|')
    }
    jest.advanceTimersByTime(1000)

    return calls.join(' ')
}

describe('setupLoaderInterceptors()', () => {
    beforeEach(() => jest.useFakeTimers())
    afterEach(() => jest.useRealTimers())

    it('finishes at once for a single successful request', () => {
        expect(run(['req', 200])).toBe('start | finish |')
    })

    it('sets a first step, then increases, then finishes late for parallel requests', () => {
        expect(run(['req', 'req', 'req', 200, 200, 200])).toBe('start | | | set 60 | increase 5 | | finish')
    })

    it('uses smaller steps the more requests are in flight', () => {
        expect(run(['req', 'req', 'req', 'req', 'req', 'req', 'req', 200, 200, 200]))
            .toBe('start | | | | | | | set 30 | increase 3 | increase 3 |')
        expect(run(['req', 'req', 'req', 'req', 'req', 200, 200]))
            .toBe('start | | | | | set 50 | increase 4 |')
    })

    it('does not finish on a non-200 final response', () => {
        expect(run(['req', 201])).toBe('start | set 60 |')
        expect(run(['req', 'req', 200, 201])).toBe('start | | set 60 | increase 5 |')
    })

    it('finishes and resets when responses outnumber requests', () => {
        expect(run(['req', 201, 201, 'req', 200])).toBe('start | set 60 | | start | finish | finish') // the reset's finish runs 100ms later
    })

    it('restarts the bar when it went idle mid-batch', () => {
        expect(run(['req', 'req', 'req', 200, 'idle', 200, 200])).toBe('start | | | set 60 | | start increase 5 | | finish')
    })

    it('finishes and resets on an error', () => {
        expect(run(['req', 'req', 'err', 'req', 200])).toBe('start | | finish | start | finish |')
    })

    it('force-finishes a bar stuck for 90 seconds', () => {
        expect(run(['req', 'req', 200, 'wait90s', 'req', 200])).toBe('start | | set 60 | finish | start | finish |')
    })
})
