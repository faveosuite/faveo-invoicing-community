export function webhookUrlRules(url, t) {
    return {
        editWebhookUrl: [url, { isRequired: t('message.field_required') }, { isUrl: t('message.invalid_url') }],
    }
}
