const ordinals = ['First', 'Second', 'Third', 'Fourth', 'Fifth']

export function formatAction(action) {
    if (!action) return '—'
    if (action === 'send') return 'First OTP sent'
    const retryMatch = action.match(/^retry_(\d+)$/)
    if (retryMatch) {
        const n = parseInt(retryMatch[1], 10)
        const ord = ordinals[n - 1] ?? `${n}th`
        return `${ord} retry`
    }
    return action.replace(/[-_]/g, ' ').replace(/\b\w/g, c => c.toUpperCase())
}
