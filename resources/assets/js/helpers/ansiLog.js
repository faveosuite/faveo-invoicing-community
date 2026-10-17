/**
 * Turns raw install-script output (with ANSI colour codes) into lines the deploy wizard
 * can colour, and reads the script's own conventions out of it: red = error, yellow = stage.
 */

const COLORS = { 31: 'red', 32: 'green', 33: 'yellow', 34: 'blue', 36: 'cyan' }
// eslint-disable-next-line no-control-regex
const ESCAPE = /(\x1b\[[0-9;?]*[A-Za-z])/

/** @returns {{ segments: {text: string, color: string}[], text: string, color: string }[]} */
export function parseLog(raw) {
    const lines = raw.split('\n').map(rawLine => {
        // Progress bars redraw the line with \r: keep only its final state.
        const trimmed = rawLine.replace(/\r+$/, '')
        const line    = trimmed.slice(trimmed.lastIndexOf('\r') + 1)

        const segments = []
        let color = ''
        for (const part of line.split(ESCAPE)) {
            if (part.startsWith('\x1b[')) {
                // Only colour codes matter; cursor moves etc. are dropped.
                if (part.endsWith('m')) {
                    for (const n of part.slice(2, -1).split(';')) {
                        if (n === '' || n === '0') color = ''
                        else if (COLORS[n]) color = COLORS[n]
                    }
                }
                continue
            }
            if (part) segments.push({ text: part, color })
        }

        return {
            segments,
            text:  segments.map(s => s.text).join(''),
            color: segments.find(s => s.text.trim())?.color ?? '',
        }
    })

    if (lines.length && lines.at(-1).text === '') lines.pop()
    return lines
}

/**
 * The line that explains a failed run. The script prints its error in red, then "Rolling back…"
 * and a page of red cleanup lines — so the reason is the red line just before the rollback.
 */
export function failureReason(lines) {
    const isRed  = l => l.color === 'red' && l.text.trim() !== ''
    const rollAt = lines.findIndex(l => /rolling back/i.test(l.text))

    let reason = ''
    if (rollAt !== -1) {
        // Some errors say it on one line: "Could not connect to X. Rolling back..."
        reason = lines[rollAt].text.replace(/rolling back.*$/i, '').trim()
        if (!reason) reason = lines.slice(0, rollAt).filter(isRed).at(-1)?.text.trim() ?? ''
    }
    if (!reason) reason = (lines.find(isRed) ?? lines.filter(l => l.text.trim()).at(-1))?.text.trim() ?? ''

    return { reason, rolledBack: rollAt !== -1 }
}

// The script announces each stage in yellow with an action word; its other yellow lines are notes
// ("Skipping php.ini", "Continuing with the Installation").
const STAGE = /^(Installing|Configuring|Downloading|Obtaining|Generating|Setting up|Checking)\b/

/** The stages the script has reached so far, in order, e.g. "Installing PHP and necessary extensions". */
export function stages(lines) {
    return lines
        .filter(l => l.color === 'yellow' && STAGE.test(l.text.trim()))
        .map(l => l.text.trim().replace(/[.…]+$/, '').trim())
}
