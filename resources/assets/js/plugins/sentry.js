import * as Sentry from '@sentry/vue'

export function initSentry(app, el) {
    if (!el?.dataset?.sentryDsn || el.dataset.sentryEnabled !== 'true') {
        return
    }

    Sentry.init({
        app,
        dsn: el.dataset.sentryDsn,
        release: el.dataset.appVersion || undefined,
        environment: import.meta.env.MODE,
    })
}

export { Sentry }
