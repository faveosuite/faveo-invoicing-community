import * as yup from 'yup'
import { __ } from '@/plugins/i18n'

// Same rule as UpdateCartItemRequest: a bare host with a dot (sub.domain.com) or an IPv4.
// The license validator substring-matches it against the install URL, so no scheme/path.
const HOST_RE = /^(?=.{1,253}$)([a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9]([a-z0-9-]{0,61}[a-z0-9])?$/i
const IPV4_RE = /^(25[0-5]|2[0-4]\d|1?\d?\d)(\.(25[0-5]|2[0-4]\d|1?\d?\d)){3}$/

// The last label (the TLD) needs a letter, so "123.4444.889" isn't taken for a domain.
const isHost = v => HOST_RE.test(v) && /[a-z]/i.test(v.slice(v.lastIndexOf('.') + 1))

// One required field per cart item that needs a domain, keyed `domain_<itemId>`.
export function buildCartDomainSchema(items) {
    return yup.object(Object.fromEntries(items.map(item => [
        `domain_${item.id}`,
        yup.string().trim()
           .required(() => __('message.domain_required'))
           .test('domain', () => __('message.domain_invalid'), v => !v || isHost(v) || IPV4_RE.test(v)),
    ])))
}
