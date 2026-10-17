import { parseLog, failureReason, stages } from '@/helpers/ansiLog.js'

const red    = s => `\x1b[1;31m${s}\x1b[0m`
const yellow = s => `\x1b[1;33m${s}\x1b[0m`

describe('ansiLog', () => {
    it('splits colours into segments and keeps the final state of \\r-redrawn lines', () => {
        const [line, bar] = parseLog(`${yellow(' Installing PHP ')}done\r\n10%\r55%\r100%\n`)

        expect(line.segments).toEqual([{ text: ' Installing PHP ', color: 'yellow' }, { text: 'done', color: '' }])
        expect(line.color).toBe('yellow')
        expect(bar.text).toBe('100%')
    })

    it('reports the red line before the rollback, not the cleanup after it', () => {
        const log = [
            yellow(' Downloading Faveo package... '),
            red(' Failed to download Faveo package. '),
            red(' Rolling back Faveo installation... '),
            red(' Removed Faveo files and apt sources '),
        ].join('\n')

        expect(failureReason(parseLog(log))).toEqual({ reason: 'Failed to download Faveo package.', rolledBack: true })
    })

    it('reads a reason given on the rollback line itself', () => {
        const log = red(' Could not connect to db:3306. Rolling back... ')

        expect(failureReason(parseLog(log)).reason).toBe('Could not connect to db:3306.')
    })

    it('falls back to the first red line, then the last line', () => {
        expect(failureReason(parseLog(`ok\n${red(' must be run as root ')}\n`))).toEqual({ reason: 'must be run as root', rolledBack: false })
        expect(failureReason(parseLog('ERROR 404: Not Found.\n')).reason).toBe('ERROR 404: Not Found.')
    })

    it('lists the yellow action lines as stages, skipping yellow notes', () => {
        const log = [
            yellow(' Checking OS compatibility for Faveo Helpdesk... '),
            'apt noise',
            yellow(' Skipping /etc/php.ini (not found) '),
            yellow(' Installing PHP and necessary extensions... '),
            yellow(' Continuing with the Installation '),
            yellow(' Configuring Faveo for you.. '),
        ].join('\n')

        expect(stages(parseLog(log))).toEqual([
            'Checking OS compatibility for Faveo Helpdesk',
            'Installing PHP and necessary extensions',
            'Configuring Faveo for you',
        ])
    })
})
